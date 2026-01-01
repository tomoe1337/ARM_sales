<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class CheckHeadRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response)  $next
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->isHead()) {
            return redirect()->route('dashboard')->with('error', 'У вас нет прав для доступа к этой странице');
        }

        return $next($request);
    }
}
