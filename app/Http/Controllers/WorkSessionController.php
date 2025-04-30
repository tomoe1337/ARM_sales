<?php

namespace App\Http\Controllers;

use App\Models\WorkSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkSessionController extends Controller
{
    public function start()
    {
        $user = Auth::user();
        
        if ($user->isWorking()) {
            return redirect()->route('dashboard')->with('error', 'У вас уже есть активная смена');
        }

        WorkSession::create([
            'user_id' => $user->id,
            'start_time' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Смена начата. Желаем продуктивной работы!');
    }

    public function end()
    {
        $user = Auth::user();
        $session = $user->getCurrentSession();

        if (!$session) {
            return redirect()->route('dashboard')->with('error', 'У вас нет активной смены');
        }

        $session->update([
            'end_time' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Смена завершена. Спасибо за работу!');
    }

    public function report()
    {
        if (!Auth::user()->isManager()) {
            abort(403);
        }

        $sessions = WorkSession::with('user')
            ->orderBy('start_time', 'desc')
            ->get()
            ->groupBy('user_id');

        return view('work-sessions.report', compact('sessions'));
    }
} 