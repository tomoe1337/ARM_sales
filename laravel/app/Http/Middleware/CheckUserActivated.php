<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActivated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // 1. Проверка личной активности пользователя
        if (!$user->is_active) {
            return redirect()->route('dashboard')->with('error', 'Ваш аккаунт заморожен.');
        }

        // 2. Проверка подписки отдела
        // Если у пользователя есть отдел, проверяем его подписку
        if ($user->department_id) {
            $department = $user->department;
            
            if (!$department || !$department->getActiveSubscription()) {
                return redirect()->route('unActivatedUser');
            }
        }

        return $next($request);
    }
}
