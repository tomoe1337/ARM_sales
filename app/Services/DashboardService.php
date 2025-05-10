php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Deal;
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
            ? User::where('id', '!=', $user->id)->get()
            : collect();

        $dashboardData = [
            'user' => $user,
            'employees' => $employees,
        ];

        if ($user->isWorking() || $user->isHead()) {
            if ($user->isHead()) {
                $dashboardData['monthlyRevenue'] = Deal::where('status', 'won')
                    ->whereMonth('closed_at', now()->month)
                    ->whereYear('closed_at', now()->year)
                    ->sum('amount');

                $dashboardData['todayRevenue'] = Deal::where('status', 'won')
                    ->whereDate('closed_at', now()->toDateString())
                    ->sum('amount');
                $dashboardData['wonDealsCount'] = Deal::where('status', 'won')
                    ->whereMonth('closed_at', now()->month)
                    ->whereYear('closed_at', now()->year)
                    ->count();

                $dashboardData['monthlyPlan'] = Plan::sum('monthly_plan');
                $dashboardData['percentageCompleted'] = $dashboardData['monthlyPlan'] > 0
                    ? round(($dashboardData['monthlyRevenue'] / $dashboardData['monthlyPlan']) * 100, 2)
                    : 0;
            } else {
                $dashboardData['monthlyRevenue'] = Deal::where('user_id', $user->id)
                    ->where('status', 'won')
                    ->whereMonth('closed_at', now()->month)
                    ->whereYear('closed_at', now()->year)
                    ->sum('amount');

                $dashboardData['todayRevenue'] = Deal::where('user_id', $user->id)
                    ->where('status', 'won')
                    ->whereDate('closed_at', now()->toDateString())
                    ->sum('amount');

                $dashboardData['wonDealsCount'] = Deal::where('user_id', $user->id)
                    ->where('status', 'won')
                    ->whereMonth('closed_at', now()->month)
                    ->whereYear('closed_at', now()->year)
                    ->count();

                $plan = Plan::where('user_id', $user->id)->first();
                $dashboardData['monthlyPlan'] = $plan ? $plan->monthly_plan : 0;
                $dashboardData['percentageCompleted'] = $dashboardData['monthlyPlan'] > 0
                    ? round(($dashboardData['monthlyRevenue'] / $dashboardData['monthlyPlan']) * 100, 2)
                    : 0;
            }

            $dashboardData['dealsCount'] = Deal::where('user_id', $user->id)->count();
            $dashboardData['clientsCount'] = Client::where('user_id', $user->id)->count();

            $dashboardData['activeTasks'] = $user->isHead() ? Task::where('status', '!=', 'completed')->with(['assignee'])->latest()->take(5)->get() : Task::where('user_id', $user->id)->where('status', '!=', 'completed')->latest()->take(5)->get();
            $dashboardData['latestDeals'] = $user->isHead() ? Deal::with('client')->latest()->take(5)->get() : Deal::where('user_id', $user->id)->with('client')->latest()->take(5)->get();
            $dashboardData['latestClients'] = $user->isHead() ? Client::withCount('deals')->latest()->take(5)->get() : Client::where('user_id', $user->id)->withCount('deals')->latest()->take(5)->get();
            $dashboardData['activeTasksCount'] = $dashboardData['activeTasks']->count();
        }

        return $dashboardData;
    }
}