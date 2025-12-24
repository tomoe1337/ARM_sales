<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Deal;
use App\Models\User;
use App\Observers\DealObserver;
use App\Observers\UserObserver;

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
        \Log::info('AppServiceProvider boot method called');
        Deal::observe(DealObserver::class);
        User::observe(UserObserver::class);
    }
}
