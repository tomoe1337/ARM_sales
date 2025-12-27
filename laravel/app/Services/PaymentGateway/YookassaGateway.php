<?php

namespace App\Services\PaymentGateway;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use YooKassa\Client;
use Exception;
use YooKassa\Model\Notification\NotificationEventType;
use YooKassa\Model\Notification\NotificationSucceeded;
use YooKassa\Model\Notification\NotificationWaitingForCapture;

class YookassaGateway extends AbstractGateway
{
    private string $shopId;
    private string $secretKey;
    private Client $client;

    public function __construct()
    {
        parent::__construct();

        $this->shopId = (string)Config::get('services.yookassa.shop_id');
        $this->secretKey = (string)Config::get('services.yookassa.secret_key');

        $this->client = new Client();
        $this->client->setAuth($this->shopId, $this->secretKey);
    }

    protected function getGatewayName(): string
    {
        return 'Yookassa';
    }

    /**
     * Создает новый платеж в ЮKassa
     */
    public function createPayment(array $data): Payment
    {
        Log::info('=== YOOKASSA createPayment STARTED ===', [
            'subscription_id' => $data['subscription_id'],
            'amount' => $data['amount'],
        ]);

        try {
            $subscription = Subscription::findOrFail($data['subscription_id']);

            // Создаем запись платежа в нашей БД
            $payment = Payment::create([
                'organization_id' => $subscription->organization_id,
                'paid_by' => auth()->id(),
                'payment_provider' => 'yookassa',
                'status' => 'pending',
                'amount' => $data['amount'],
                'currency' => 'RUB',
                'period_start' => $subscription->starts_at->toDateString(),
                'period_end' => $subscription->ends_at->toDateString(),
                'months' => $data['months'],
                'provider_data' => [
                    'user_limit' => $data['user_limit'],
                    'subscription_id' => $subscription->id,
                ],
            ]);

            $payment->subscriptions()->attach($subscription->id, [
                'amount' => $data['amount'],
            ]);

            // Запрос в ЮKassa
            $yooPayment = $this->client->createPayment(
                [
                    'amount' => [
                        'value' => number_format($data['amount'], 2, '.', ''),
                        'currency' => 'RUB',
                    ],
                    'confirmation' => [
                        'type' => 'redirect',
                        'return_url' => route('payment.check', ['payment' => $payment->id]),
                    ],
                    'capture' => true,
                    'description' => $data['description'],
                    'metadata' => [
                        'payment_id' => $payment->id,
                    ],
                ],
                uniqid('', true) // Идемпотентность
            );

            $paymentUrl = $yooPayment->getConfirmation()->getConfirmationUrl();

            // Сохраняем внешний ID платежа
            $payment->update([
                'payment_id' => $yooPayment->getId(),
                'provider_data' => array_merge($payment->provider_data ?? [], [
                    'payment_url' => $paymentUrl,
                ]),
            ]);

            return $payment->fresh();

        } catch (Exception $e) {
            Log::error('Ошибка создания платежа ЮKassa', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Обрабатывает webhook от ЮKassa
     */
    public function handleCallback(array $data): Payment
    {
        Log::info('Получен вебхук от ЮKassa', $data);

        try {
            // Если $data пуста (такое бывает если JSON не распарсился Laravel),
            // пробуем прочитать тело напрямую
            if (empty($data)) {
                $source = file_get_contents('php://input');
                $data = json_decode($source, true);
            }

            if (!$data) {
                throw new Exception('Empty or invalid data in YooKassa webhook');
            }

            $notification = ($data['event'] === NotificationEventType::PAYMENT_SUCCEEDED)
                ? new NotificationSucceeded($data)
                : new NotificationWaitingForCapture($data);

            $paymentObject = $notification->getObject();
            $externalId = $paymentObject->getId();

            if ($data['event'] !== NotificationEventType::PAYMENT_SUCCEEDED) {
                Log::info('ЮKassa: игнорируем событие', ['event' => $data['event']]);
                return Payment::where('payment_id', $externalId)->firstOrFail();
            }

            $payment = Payment::where('payment_id', $externalId)->firstOrFail();

            if ($payment->status === 'succeeded') {
                return $payment;
            }

            // Обновляем статус
            $payment->update([
                'status' => 'succeeded',
                'paid_at' => now(),
            ]);

            // Активируем подписки
            foreach ($payment->subscriptions as $subscription) {
                $userLimit = $payment->provider_data['user_limit'] ?? $subscription->paid_users_limit;
                $months = $payment->months ?? 1;

                $startsAt = $subscription->isTrial() || $subscription->isExpired() ? now() : $subscription->starts_at;
                $endsAt = $subscription->isActive()
                    ? $subscription->ends_at->copy()->addMonths($months)
                    : $startsAt->copy()->addMonths($months);

                $subscription->update([
                    'status' => 'active',
                    'paid_by' => $payment->paid_by,
                    'paid_users_limit' => $userLimit,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                ]);
            }

            return $payment->fresh();

        } catch (Exception $e) {
            Log::error('Ошибка обработки вебхука ЮKassa', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}

