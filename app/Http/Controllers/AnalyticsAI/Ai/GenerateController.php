<?php

namespace App\Http\Controllers\AnalyticsAI\Ai;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsAiService;
use Illuminate\Http\Request;


class GenerateController extends Controller
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
        $this->analyticsAiService->generateAiReport();

        return redirect()->route('analyticsAi.index');
    }
}
