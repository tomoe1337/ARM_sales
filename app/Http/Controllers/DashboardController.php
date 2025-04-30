<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $employees = $user->isHead()
            ? User::where('id', '!=', $user->id)->get()
            : collect();

        if ($user->isWorking() || $user->isHead()) {
            // Для руководителя считаем общую выручку отдела
            if ($user->isHead()) {
                $monthlyRevenue = Deal::where('status', 'won')
                    ->whereMonth('closed_at', now()->month)
                    ->whereYear('closed_at', now()->year)
                    ->sum('amount');
                
                $todayRevenue = Deal::where('status', 'won')
                    ->whereDate('closed_at', now()->toDateString())
                    ->sum('amount');
                $wonDealsCount = Deal::where('status', 'won')
                    ->whereMonth('closed_at', now()->month)
                    ->whereYear('closed_at', now()->year)
                    ->count();
                
                // Получаем общий план отдела
                $monthlyPlan = Plan::sum('monthly_plan');
                $percentageCompleted = $monthlyPlan > 0 
                    ? round(($monthlyRevenue / $monthlyPlan) * 100, 2)
                    : 0;
            } else {
                // Для обычного пользователя считаем только его выручку
                $monthlyRevenue = Deal::where('user_id', $user->id)
                    ->where('status', 'won')
                    ->whereMonth('closed_at', now()->month)
                    ->whereYear('closed_at', now()->year)
                    ->sum('amount');

                $todayRevenue = Deal::where('user_id', $user->id)
                    ->where('status', 'won')
                    ->whereDate('closed_at', now()->toDateString())
                    ->sum('amount');

                $wonDealsCount = Deal::where('user_id', $user->id)
                    ->where('status', 'won')
                    ->whereMonth('closed_at', now()->month)
                    ->whereYear('closed_at', now()->year)
                    ->count();
                
                // Получаем план пользователя
                $plan = Plan::where('user_id', $user->id)->first();
                $monthlyPlan = $plan ? $plan->monthly_plan : 0;
                $percentageCompleted = $monthlyPlan > 0 
                    ? round(($monthlyRevenue / $monthlyPlan) * 100, 2)
                    : 0;
            }

            $dealsCount = Deal::where('user_id', $user->id)->count();
            $clientsCount = Client::where('user_id', $user->id)->count();
            
            // Получаем задачи в зависимости от роли пользователя
            if ($user->isHead()) {
                // Руководитель видит все невыполненные задачи
                $activeTasks = Task::where('status', '!=', 'completed')
                    ->with(['assignee'])
                    ->latest()
                    ->take(5)
                    ->get();
            } else {
                // Обычный пользователь видит только свои задачи
                $activeTasks = Task::where('user_id', $user->id)
                    ->where('status', '!=', 'completed')
                    ->latest()
                    ->take(5)
                    ->get();
            }

            $latestDeals = $user->isHead()
                ? Deal::with('client')
                    ->latest()
                    ->take(5)
                    ->get()
                : Deal::where('user_id', $user->id)
                    ->with('client')
                    ->latest()
                    ->take(5)
                    ->get();

            $latestClients = $user->isHead()
                ? Client::withCount('deals')
                    ->latest()
                    ->take(5)
                    ->get()
                : Client::where('user_id', $user->id)
                    ->withCount('deals')
                    ->latest()
                    ->take(5)
                    ->get();

            // Подсчет количества активных задач
            $activeTasksCount = $activeTasks->count();

            return view('dashboard', compact(
                'user',
                'employees',
                'monthlyRevenue',
                'todayRevenue',
                'monthlyPlan',
                'percentageCompleted',
                'dealsCount',
                'wonDealsCount',
                'clientsCount',
                'activeTasks',
                'latestDeals',
                'latestClients',
                'activeTasksCount'
            ));
        }

        return view('dashboard', compact('user', 'employees'));
    }
} 