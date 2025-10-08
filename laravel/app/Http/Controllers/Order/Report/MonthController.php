<?php

namespace App\Http\Controllers\Order\Report;

use App\Services\OrderService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonthController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $reportData = $this->orderService->getMonthReportData($user);

        return view('orders.reports.month', compact('reportData'));
    }
}