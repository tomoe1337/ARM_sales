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

        $this->orderService->updateOrder($order, $request->validated());

        return redirect()->route('orders.show', $order)->with('success', 'Заказ успешно обновлен');
    }
}