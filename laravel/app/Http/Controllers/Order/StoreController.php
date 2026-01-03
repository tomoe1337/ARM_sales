<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function __invoke(StoreOrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder($request->validated());
            
            return redirect()->route('orders.index')->with('success', 'Заказ успешно создан');
        } catch (\Exception $e) {
            // Логируем ошибку для отладки
            \Log::error('Failed to create order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->validated()
            ]);

            // Формируем понятное сообщение для пользователя
            $errorMessage = 'Не удалось создать заказ.';
            
            // Если ошибка связана с BlueSales, делаем сообщение более понятным
            if (str_contains($e->getMessage(), 'BlueSales')) {
                $errorMessage = 'Не удалось создать заказ. Проблема с синхронизацией с BlueSales. Проверьте, что у клиента есть bluesales_id и попробуйте позже.';
            } elseif (str_contains($e->getMessage(), 'bluesales_id')) {
                $errorMessage = 'Не удалось создать заказ. У клиента отсутствует bluesales_id. Сначала синхронизируйте клиента с BlueSales.';
            } else {
                // Для других ошибок показываем общее сообщение
                $errorMessage = 'Не удалось создать заказ. Проверьте введенные данные и попробуйте снова.';
            }

            // Возвращаем пользователя на страницу создания с сообщением об ошибке
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }
}