<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    /**
     * Логика BlueSales: все заказы считаются оплаченными.
     * Так как BlueSales API не предоставляет информацию о типах статусов,
     * заказ создается в момент получения оплаты как факт оплаты.
     */
    public function createOrder(array $data)
    {
        // Если organization_id и department_id не указаны, берем из client_id или user_id
        if (!isset($data['organization_id']) || !isset($data['department_id'])) {
            if (isset($data['client_id'])) {
                $client = Client::find($data['client_id']);
                if ($client) {
                    $data['organization_id'] = $data['organization_id'] ?? $client->organization_id;
                    $data['department_id'] = $data['department_id'] ?? $client->department_id;
                }
            } elseif (isset($data['user_id'])) {
                $user = User::find($data['user_id']);
                if ($user) {
                    $data['organization_id'] = $data['organization_id'] ?? $user->organization_id;
                    $data['department_id'] = $data['department_id'] ?? $user->department_id;
                }
            } elseif (Auth::check()) {
                $user = Auth::user();
                $data['organization_id'] = $data['organization_id'] ?? $user->organization_id;
                $data['department_id'] = $data['department_id'] ?? $user->department_id;
            }
        }
        
        $order = Order::create($data);
        return $order;
    }

    public function updateOrder(Order $order, array $data)
    {
        return $order->update($data);
    }

    public function deleteOrder(Order $order): bool
    {
        return $order->delete();
    }

    public function getDayReportData(\App\Models\User $user): array
    {
        // Логика: все заказы BlueSales считаются оплаченными
        $todayRevenue = Order::where('user_id', $user->id)
            ->whereDate('updated_at', now()->toDateString())
            ->sum('total_amount');

        return compact('todayRevenue');
    }

    public function getMonthReportData(\App\Models\User $user): array
    {
        // Логика: все заказы BlueSales считаются оплаченными
        $monthlyRevenue = Order::where('user_id', $user->id)
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('total_amount');

        return compact('monthlyRevenue');
    }

    public function getTimeReportData(\App\Models\User $user): array
    {
        // The original controller's time report logic was not provided.
        // Implement the logic here based on the required time period.
        return []; // Placeholder
    }
}