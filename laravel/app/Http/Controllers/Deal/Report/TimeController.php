<?php

namespace App\Http\Controllers\Deal\Report;

use App\Http\Controllers\Controller;
use App\Services\DealService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimeController extends Controller
{
    protected $dealService;

    public function __construct(DealService $dealService)
    {
        $this->dealService = $dealService;
    }

    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $reportData = $this->dealService->getTimeReportData($user, $request); // Передача Request в сервис
        return view('deals.reports.time', compact('reportData'));
    }
}