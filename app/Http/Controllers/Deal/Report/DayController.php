php
<?php

namespace App\Http\Controllers\Deal\Report;

use App\Http\Controllers\Controller;
use App\Services\DealService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DayController extends Controller
{
    protected $dealService;

    public function __construct(DealService $dealService)
    {
        $this->dealService = $dealService;
    }

    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $reportData = $this->dealService->getDayReportData($user);

        return view('deals.reports.day', compact('reportData'));
    }
}