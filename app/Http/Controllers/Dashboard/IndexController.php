php
<?php

namespace App\Http\Controllers\Dashboard;

use App\Services\DashboardService;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = auth()->user();
        $dashboardData = $this->dashboardService->getDashboardData($user);
        return view('dashboard.index', compact('dashboardData'));
    }
}