<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentGateway\PaymentGatewayFactory;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentController extends Controller
{
    /**
     * Проверка статуса платежа после возврата пользователя из ЮKassa
     * 
     * @param Payment $payment
     * @return RedirectResponse
     */
    public function checkStatus(Payment $payment): RedirectResponse
    {
        // Даем вебхуку время дойти (ждем до 2 секунд, проверяя базу)
        for ($i = 0; $i < 4; $i++) {
            $payment->refresh();
            if ($payment->status === 'succeeded') {
                return redirect()->route('payment.success');
            }
            usleep(500000); // пауза 0.5 сек
        }

        // Если через 2 секунды всё еще не оплачен
        if ($payment->status === 'succeeded') {
            return redirect()->route('payment.success');
        }

        return redirect()->route('payment.failed');
    }

    /**
     * Webhook от платежной системы
     * 
     * POST /api/payments/callback/{gateway}
     * Например: /api/payments/callback/robokassa
     */
    public function callback(Request $request, string $gateway): JsonResponse
    {
        try {
            $logChannel = in_array($gateway, ['robokassa', 'yookassa']) ? $gateway : 'single';
            
            Log::channel($logChannel)->info('Входящий webhook', [
                'gateway' => $gateway,
                'data' => $request->all(),
            ]);

            $paymentGateway = PaymentGatewayFactory::create($gateway);
            $payment = $paymentGateway->handleCallback($request->all());

            if ($gateway === 'robokassa') {
                return response()->json([
                    'success' => true,
                    'message' => 'OK' . $payment->id,
                ]);
            }

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            $logChannel = ($gateway === 'robokassa') ? 'robokassa' : 'single';
            Log::channel($logChannel)->error('Ошибка обработки webhook', [
                'gateway' => $gateway,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Для платежных систем лучше возвращать 200, даже если у нас ошибка, 
            // чтобы они не заваливали нас повторами (если мы логируем ошибку у себя)
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }
}

