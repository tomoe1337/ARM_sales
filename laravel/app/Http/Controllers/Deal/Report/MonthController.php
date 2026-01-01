<?php

namespace App\Http\Controllers\Deal\Report;

use App\Services\DealService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonthController extends Controller
{
    protected $dealService;

    public function __construct(DealService $dealService)
    {
        $this->dealService = $dealService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $reportData = $this->dealService->getMonthReportData($user);

        return view('deals.reports.month', compact('reportData'));
    }
}