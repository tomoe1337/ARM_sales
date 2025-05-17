<?php

namespace App\Http\Controllers\AnalyticsAI\Ai;

use App\Http\Controllers\Controller;
use App\Models\AnalysisAiReport;
use App\Services\AnalyticsAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;


class IndexController extends Controller
{
    protected $analyticsAiService;

    public function __construct(AnalyticsAiService $analyticsAiService)
    {
        $this->analyticsAiService = $analyticsAiService;
    }

    /**
     * Display a listing of the resource.
     */
    public function __invoke(Request $request)
    {
        $weeklyReport = $this->analyticsAiService->getWeeklyReportData(true);

        $previousReports = AnalysisAiReport::where('user_id', auth()->user()->id)
            ->where('report_type', 'weekly')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'week_start' => Carbon::parse($report->start_date)->format('d.m.Y'),
                    'week_end' => Carbon::parse($report->end_date)->format('d.m.Y'),
                    'total_deals' => $report->total_leads,
                    'successful_deals' => $report->won_count,
                    'conversion_rate' => $report->total_leads > 0
                        ? number_format(($report->won_count / $report->total_leads) * 100, 1) . '%'
                        : '0%',
                    'revenue' => $report->revenue,
                ];
            })
            ->values();
        $previousReports = $previousReports->toArray();
        return view('analyticsAI.index', compact('weeklyReport', 'previousReports'));
    }
}
