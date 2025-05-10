php
<?php

namespace App\Http\Controllers\WorkSession;

use App\Http\Controllers\Controller;
use App\Services\WorkSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EndController extends Controller
{
    protected $workSessionService;

    public function __construct(WorkSessionService $workSessionService)
    {
        $this->workSessionService = $workSessionService;
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $session = $user->getCurrentSession();

        if (!$session) {
            return redirect()->route('dashboard')->with('error', 'У вас нет активной смены');
        }

        $this->workSessionService->endSession($session);

        return redirect()->route('dashboard')->with('success', 'Смена завершена. Спасибо за работу!');
    }
}