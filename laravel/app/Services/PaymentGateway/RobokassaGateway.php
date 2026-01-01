<?php

namespace App\Services\PaymentGateway;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Exception;
use Robokassa\Robokassa;

class RobokassaGateway extends AbstractGateway
{
    private string $merchantLogin;
    private string $passwordOne;
    private string $passwordTwo;

    public function __construct()
    {
        parent::__construct();

        $this->merchantLogin = Config::get('services.robokassa.merchant_login');
        $this->passwordOne = Config::get('services.robokassa.password_1');
        $this->passwordTwo = Config::get('services.robokassa.password_2');
    }

    protected function getGatewayName(): string
    {
        return 'Robokassa';
    }

    /**
     * Создает новый платеж в Робокассе для подписки
     *
     * @param array $data [
     *   'subscription_id' => int,
     *   'amount' => float,
     *   'description' => string,
     *   'months' => int,
     *   'user_limit' => int
     * ]
     */
    public function createPayment(array $data): Payment
    {
        Log::channel('robokassa')->info('=== ROBOKASSA createPayment STARTED ===', [
            'subscription_id' => $data['subscription_id'],
            'amount' => $data['amount'],
        ]);

        try {
            $subscription = Subscription::findOrFail($data['subscription_id']);

            // Создаем запись платежа
            $payment = Payment::create([
                'organization_id' => $subscription->organization_id,
                'paid_by' => auth()->id(),
                'payment_provider' => 'robokassa',
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

            // Связываем платеж с подпиской
            $payment->subscriptions()->attach($subscription->id, [
                'amount' => $data['amount'],
            ]);

            // ПРЯМОЙ РЕДИРЕКТ (STANDARD INTERFACE)
            // Самый надежный способ, обходящий баги любых SDK.
            $outSum = number_format($data['amount'], 2, '.', '');
            $invId = (int)$payment->id;
            
            // Формула: MerchantLogin:OutSum:InvId:Password1
            $sigString = "{$this->merchantLogin}:{$outSum}:{$invId}:{$this->passwordOne}";
            $signature = md5($sigString);
            
            $paymentUrl = "https://auth.robokassa.ru/Merchant/Index.aspx?" . http_build_query([
                'MerchantLogin' => $this->merchantLogin,
                'OutSum' => $outSum,
                'InvId' => $invId,
                'Description' => $data['description'],
                'Culture' => 'ru',
                'IsTest' => $this->isSandboxMode() ? 1 : 0,
                'SignatureValue' => $signature,
            ]);

            Log::channel('robokassa')->info('Payment link generated manually', [
                'url' => $paymentUrl,
                'sig_string' => "{$this->merchantLogin}:{$outSum}:{$invId}:***"
            ]);

            // Сохраняем URL и payment_id
            $payment->update([
                'payment_id' => (string)$payment->id,
                'provider_data' => array_merge($payment->provider_data ?? [], [
                    'payment_url' => $paymentUrl,
                ]),
            ]);

            return $payment->fresh();

        } catch (Exception $e) {
            Log::channel('robokassa')->error('Ошибка создания платежа Робокасса', [
                'subscription_id' => $data['subscription_id'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Обрабатывает webhook от Робокассы
     */
    public function handleCallback(array $data): Payment
    {
        Log::channel('robokassa')->info('Получен вебхук от Robokassa', $data);

        $invId = $data['InvId'];
        $outSum = $data['OutSum'];
        $signatureValue = $data['SignatureValue'];

        // Проверка подписи (для вебхука используется Password #2)
        // Формула: OutSum:InvId:Password2
        $expectedSignature = strtoupper(md5("{$outSum}:{$invId}:{$this->passwordTwo}"));

        if (strtoupper($signatureValue) !== $expectedSignature) {
            Log::channel('robokassa')->error('Неверная подпись в вебхуке Робокассы', [
                'expected' => $expectedSignature,
                'received' => strtoupper($signatureValue),
            ]);
            abort(403, 'Неверная подпись');
        }

        try {
            $payment = Payment::where('payment_id', $invId)->firstOrFail();

            if ($payment->status === 'succeeded') {
                return $payment;
            }

            $payment->update([
                'status' => 'succeeded',
                'paid_at' => now(),
            ]);

            // Отмечаем в организации, что триал использован
            if ($payment->organization && !$payment->organization->trial_used_at) {
                $payment->organization->update(['trial_used_at' => now()]);
            }

            foreach ($payment->subscriptions as $subscription) {
                $userLimit = $payment->provider_data['user_limit'] ?? $subscription->paid_users_limit;
                $months = $payment->months ?? 1;

                $isTrialOrExpired = $subscription->isTrial() || $subscription->isExpired();
                
                $startsAt = $isTrialOrExpired ? now() : $subscription->starts_at;
                $endsAt = $isTrialOrExpired 
                    ? now()->copy()->addMonths($months) 
                    : $subscription->ends_at->copy()->addMonths($months);

                $subscription->update([
                    'status' => 'active',
                    'paid_by' => $payment->paid_by,
                    'paid_users_limit' => $userLimit,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'trial_ends_at' => null, // Убираем статус пробного периода
                ]);
            }

            return $payment->fresh();

        } catch (Exception $e) {
            Log::channel('robokassa')->error('Ошибка обработки вебхука Robokassa', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
