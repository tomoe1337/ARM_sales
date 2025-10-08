<?php

namespace App\Http\Controllers\AnalyticsAI\Ai;

use App\Http\Controllers\Controller;
use App\Models\AnalysisAiReport;
use App\Services\AnalyticsAiService;
use Illuminate\Http\Request;


class ShowController extends Controller
{
    protected $analyticsAiService;

    public function __construct(AnalyticsAiService $analyticsAiService)
    {
        $this->analyticsAiService = $analyticsAiService;
    }

    /**
     * Display a listing of the resource.
     */
    public function __invoke(AnalysisAiReport $analysisAiReport)
    {
        $data = $analysisAiReport;
        $analysis = [
            'done_well' => $data['done_well'],
            'done_bad' => $data['done_bad'],
            'general_result' => $data['general_result'],
        ];

        $funnel = [
            'all_leads' => $data->total_leads,
            'in_progress' => $data->in_progress_count,
            'won' => $data->won_count,
            'lost' => $data->lost_count,
        ];
        $employeeStats = $data['employee_stats'];

        return view('analyticsAi.report', compact('analysis', 'funnel', 'employeeStats'));
    }
}
