<?php

namespace App\Http\Controllers\WorkSession;

use App\Models\User;
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
    public function __invoke(?User $user = null): View
    {
        if (!Auth::user()->isHead()) {
            abort(403);
        }

        $sessions = $this->workSessionService->getReportData($user);

        return view('work-sessions.report', compact('sessions', 'user'));
    }
}
