<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DestroyController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Order $order)
    {
        if (!$order->canDelete(Auth::user())) {
            abort(403, 'У вас нет прав для удаления этого заказа');
        }

        $this->orderService->deleteOrder($order);

        return redirect()->route('orders.index')->with('success', 'Заказ успешно удален');
    }
}