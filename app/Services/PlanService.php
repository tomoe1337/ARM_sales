<?php

namespace App\Services;

use App\Models\User;
use App\Models\Plan;

class PlanService
{
    public function getIndexData(): array
    {
        $managers = User::where('role', 'manager')->get();
        $plans = Plan::whereIn('user_id', $managers->pluck('id'))->get()->keyBy('user_id');

        $totalMonthlyPlan = $plans->sum('monthly_plan');
        $totalDailyPlan = $plans->sum('daily_plan');

        return compact('managers', 'plans', 'totalMonthlyPlan', 'totalDailyPlan');
    }

    public function createPlan(array $data): Plan
    {
        $data['user_id'] = auth()->id();

        return Plan::create($data);
    }

    public function updatePlanForUser(User $user, array $data): Plan
    {
        $plan = Plan::firstOrNew(['user_id' => $user->id]);

        $plan->fill($data);
        $plan->save();

        return $plan;
    }

    public function deletePlan(Plan $plan): bool
    {
        return $plan->delete();
    }

}