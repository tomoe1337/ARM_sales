<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Models\Deal;
use App\Models\User;
use App\Observers\DealObserver;
use App\Observers\UserObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Форсировать HTTPS для ngrok и production
        if (config('app.env') === 'production' || str_contains(config('app.url'), 'ngrok')) {
            URL::forceScheme('https');
        }

        // Определение лимитера для API
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        \Log::info('AppServiceProvider boot method called');
        Deal::observe(DealObserver::class);
        User::observe(UserObserver::class);
    }
}
