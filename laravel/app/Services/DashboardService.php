<?php

namespace App\Services;

use App\Models\User;
use App\Models\Deal;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Client;
use App\Models\Task;

class DashboardService
{
    /**
     * Get data for the dashboard.
     *
     * @return array
     */
    public function getDashboardData(User $user): array
    {
        $employees = $user->isHead()
            ? User::where('department_id', $user->department_id)
                ->where('id', '!=', $user->id)
                ->get()
            : collect();

        $dashboardData = [
            'user' => $user,
            'employees' => $employees,
        ];
        if ($user->isWorking() || $user->isHead()) {
            // Логика BlueSales: все заказы считаются оплаченными
            // Так как BlueSales API не предоставляет информацию о типах статусов,
            // заказ создается в момент получения оплаты как факт оплаты
            if ($user->isHead()) {
                $dashboardData['monthlyRevenue'] = Order::whereMonth('updated_at', now()->month)
                    ->whereYear('updated_at', now()->year)
                    ->sum('total_amount');

                $dashboardData['todayRevenue'] = Order::whereDate('updated_at', now()->toDateString())
                    ->sum('total_amount');
                    
                $dashboardData['wonDealsCount'] = Order::whereMonth('updated_at', now()->month)
                    ->whereYear('updated_at', now()->year)
                    ->count();

                $dashboardData['monthlyPlan'] = Plan::sum('monthly_plan');
                $dashboardData['percentageCompleted'] = $dashboardData['monthlyPlan'] > 0
                    ? round(($dashboardData['monthlyRevenue'] / $dashboardData['monthlyPlan']) * 100, 2)
                    : 0;
            } else {
                $dashboardData['monthlyRevenue'] = Order::where('user_id', $user->id)
                    ->whereMonth('updated_at', now()->month)
                    ->whereYear('updated_at', now()->year)
                    ->sum('total_amount');

                $dashboardData['todayRevenue'] = Order::where('user_id', $user->id)
                    ->whereDate('updated_at', now()->toDateString())
                    ->sum('total_amount');

                $dashboardData['wonDealsCount'] = Order::where('user_id', $user->id)
                    ->whereMonth('updated_at', now()->month)
                    ->whereYear('updated_at', now()->year)
                    ->count();

                $plan = Plan::where('user_id', $user->id)->first();
                $dashboardData['monthlyPlan'] = $plan ? $plan->monthly_plan : 0;
                $dashboardData['percentageCompleted'] = $dashboardData['monthlyPlan'] > 0
                    ? round(($dashboardData['monthlyRevenue'] / $dashboardData['monthlyPlan']) * 100, 2)
                    : 0;
            }

            $dashboardData['ordersCount'] = Order::where('user_id', $user->id)->count();
            $dashboardData['clientsCount'] = Client::where('user_id', $user->id)->count();

            $dashboardData['activeTasks'] = $user->isHead() ? Task::where('status', '!=', 'completed')->with(['assignee'])->latest()->take(5)->get() : Task::where('user_id', $user->id)->where('status', '!=', 'completed')->latest()->take(5)->get();
            $dashboardData['latestOrders'] = $user->isHead() ? Order::with('client')->latest()->take(5)->get() : Order::where('user_id', $user->id)->with('client')->latest()->take(5)->get();
            $dashboardData['latestClients'] = $user->isHead() ? Client::withCount('orders')->latest()->take(5)->get() : Client::where('user_id', $user->id)->withCount('orders')->latest()->take(5)->get();
            $dashboardData['activeTasksCount'] = $dashboardData['activeTasks']->count();
        }

        return $dashboardData;
    }
}
