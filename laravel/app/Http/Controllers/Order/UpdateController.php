<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;

class UpdateController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateOrderRequest $request, Order $order)
    {
        if (! $order->canEdit(Auth::user())) {
             abort(403, 'У вас нет прав для редактирования этого заказа');
        }

        try {
            $this->orderService->updateOrder($order, $request->validated());

            return redirect()->route('orders.show', $order)->with('success', 'Заказ успешно обновлен');
        } catch (\Exception $e) {
            // Логируем ошибку для отладки
            \Log::error('Failed to update order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $order->id,
                'data' => $request->validated()
            ]);

            // Формируем понятное сообщение для пользователя
            $errorMessage = 'Не удалось обновить заказ. Проверьте введенные данные и попробуйте снова.';

            // Возвращаем пользователя на страницу редактирования с сообщением об ошибке
            return redirect()
                ->route('orders.edit', $order)
                ->withInput()
                ->with('error', $errorMessage);
        }
    }
}