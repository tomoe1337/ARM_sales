<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckWorkSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем, начал ли пользователь смену
        // Предполагается, что пользователь уже аутентифицирован на этом этапе
        if (Auth::check() && !Auth::user()->isWorking()) {
            // Если пользователь аутентифицирован, но не начал смену, перенаправляем
            // его на дашборд с сообщением об ошибке
            return redirect()->route('dashboard')->with('error', 'Для доступа к этой странице необходимо начать смену.');
        }

        return $next($request);
    }
}