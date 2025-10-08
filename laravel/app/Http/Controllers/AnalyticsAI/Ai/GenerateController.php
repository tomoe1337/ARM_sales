<?php

namespace App\Http\Controllers\AnalyticsAI\Ai;

use App\Http\Controllers\Controller;
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
        $this->analyticsAiService->generateAiReport();

        return redirect()->route('analyticsAi.index');
    }
}
