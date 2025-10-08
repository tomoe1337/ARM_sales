<?php

namespace App\Http\Controllers\Order\Report;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DayController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $reportData = $this->orderService->getDayReportData($user);

        return view('orders.reports.day', compact('reportData'));
    }
}