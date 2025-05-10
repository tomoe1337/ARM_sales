php
<?php

namespace App\Services;

use App\Models\Deal;
use Illuminate\Support\Facades\Auth;

class DealService
{
    public function createDeal(array $data)
    {
        $data['user_id'] = Auth::id();
        $deal = Deal::create($data);
        return $deal;
    }

    public function updateDeal(Deal $deal, array $data)
    {
        return $deal->update($data);
    }

    public function deleteDeal(Deal $deal): bool
    {
        return $deal->delete();
    }

    public function getDayReportData(\App\Models\User $user): array
    {
        // Logic for day report data based on original DealController
        $todayRevenue = Deal::where('user_id', $user->id)
            ->where('status', 'won')
            ->whereDate('closed_at', now()->toDateString())
            ->sum('amount');

        return compact('todayRevenue');
    }

    public function getMonthReportData(\App\Models\User $user): array
    {
        // Logic for month report data based on original DealController
        $monthlyRevenue = Deal::where('user_id', $user->id)
            ->where('status', 'won')
            ->whereMonth('closed_at', now()->month)
            ->whereYear('closed_at', now()->year)
            ->sum('amount');

        return compact('monthlyRevenue');
    }

    public function getTimeReportData(\App\Models\User $user): array
    {
        // The original controller's time report logic was not provided.
        // Implement the logic here based on the required time period.
        return []; // Placeholder
    }
}