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
        $order = $this->orderService->createOrder($request->validated());
        
        return redirect()->route('orders.index')->with('success', 'Заказ успешно создан');
    }
}