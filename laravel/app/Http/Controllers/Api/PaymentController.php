<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentGateway\PaymentGatewayFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentController extends Controller
{
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

