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
                $dashboardData['monthlyRevenue'] = Order::where('department_id', $user->department_id)
                    ->whereMonth('updated_at', now()->month)
                    ->whereYear('updated_at', now()->year)
                    ->sum('total_amount');

                $dashboardData['todayRevenue'] = Order::where('department_id', $user->department_id)
                    ->whereDate('updated_at', now()->toDateString())
                    ->sum('total_amount');
                    
                $dashboardData['wonDealsCount'] = Order::where('department_id', $user->department_id)
                    ->whereMonth('updated_at', now()->month)
                    ->whereYear('updated_at', now()->year)
                    ->count();

                $dashboardData['monthlyPlan'] = Plan::where('department_id', $user->department_id)
                    ->sum('monthly_plan');
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
            $dashboardData['activeTasksCount'] = $dashboardData['activeTasks']->count();

            // Клиенты по дате следующего контакта
            $clientsQuery = $user->isHead() 
                ? Client::whereNotNull('next_contact_date')
                : Client::where('user_id', $user->id)->whereNotNull('next_contact_date');

            $today = now()->startOfDay();
            $tomorrow = now()->addDay()->startOfDay();

            $dashboardData['clientsToday'] = (clone $clientsQuery)
                ->whereDate('next_contact_date', $today->toDateString())
                ->orderBy('next_contact_date')
                ->take(10)
                ->get();

            $dashboardData['clientsTomorrow'] = (clone $clientsQuery)
                ->whereDate('next_contact_date', $tomorrow->toDateString())
                ->orderBy('next_contact_date')
                ->take(10)
                ->get();

            $dashboardData['clientsOverdue'] = (clone $clientsQuery)
                ->where('next_contact_date', '<', $today)
                ->orderBy('next_contact_date', 'asc')
                ->take(10)
                ->get();
        }

        return $dashboardData;
    }
}
