<?php

namespace App\Http\Controllers\WorkSession;

use App\Http\Controllers\Controller;
use App\Services\WorkSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StartController extends Controller
{
    protected $workSessionService;

    public function __construct(WorkSessionService $workSessionService)
    {
        $this->workSessionService = $workSessionService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();

        if ($user->isWorking()) {
            return redirect()->route('dashboard')->with('error', 'У вас уже есть активная смена');
        }

        $this->workSessionService->startSession($user);

        return redirect()->route('dashboard')->with('success', 'Смена начата. Желаем продуктивной работы!');
    }
}