php
<?php

namespace App\Http\Controllers\WorkSession;

use App\Http\Controllers\Controller;
use App\Services\WorkSessionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    protected $workSessionService;

    public function __construct(WorkSessionService $workSessionService)
    {
        $this->workSessionService = $workSessionService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        if (!Auth::user()->isHead()) {
            abort(403);
        }

        $sessions = $this->workSessionService->getGroupedWorkSessionsForReport();

        return view('work-sessions.report', compact('sessions'));
    }
}