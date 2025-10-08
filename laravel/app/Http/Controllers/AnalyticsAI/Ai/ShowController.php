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

        // Получаем реальные статусы заказов за период отчета
        $orders = \App\Models\Order::where(function ($query) use ($data) {
                $query->whereBetween('created_at', [$data->start_date, $data->end_date])
                    ->orWhereBetween('updated_at', [$data->start_date, $data->end_date]);
            })
            ->get();
        
        // Группируем заказы по статусам и считаем количество
        $statusCounts = $orders->groupBy('status')
            ->map(function ($group) {
                return $group->count();
            })
            ->sortDesc(); // Сортируем от большего к меньшему
        
        // Формируем воронку: сначала все лиды, потом статусы заказов
        $funnel = collect([
            [
                'name' => 'Все лиды',
                'count' => $data->total_leads
            ]
        ]);
        
        // Добавляем каждый статус заказа в воронку
        foreach ($statusCounts as $status => $count) {
            $funnel->push([
                'name' => $status ?: 'Без статуса',
                'count' => $count
            ]);
        }
        
        $employeeStats = $data['employee_stats'];

        return view('analyticsAI.report', compact('analysis', 'funnel', 'employeeStats'));
    }
}
