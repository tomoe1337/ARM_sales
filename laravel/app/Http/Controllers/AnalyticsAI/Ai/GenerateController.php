<?php

namespace App\Http\Controllers\AnalyticsAI\Ai;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateAiAnalyticsReport;
use App\Services\AnalyticsAiService;
use Illuminate\Http\Request;


class GenerateController extends Controller
{

    public function __construct(private AnalyticsAiService $analyticsAiService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function __invoke(Request $request)
    {
        $user = auth()->user();
        
        // Отправляем задачу в очередь
        GenerateAiAnalyticsReport::dispatch(
            $user->id,
            $user->organization_id,
            $user->department_id
        );

        return redirect()
            ->route('analyticsAi.index')
            ->with('success', 'Генерация отчета запущена. Обновите страницу через 30-60 секунд.');
    }
}
