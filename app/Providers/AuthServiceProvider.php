<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Deal;
use App\Models\Plan;
use App\Models\Task;
use App\Policies\ClientPolicy;
use App\Policies\DealPolicy;
use App\Policies\PlanPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Client::class => ClientPolicy::class,
        Deal::class => DealPolicy::class,
        Plan::class => PlanPolicy::class,
        Task::class => TaskPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        \Illuminate\Support\Facades\Auth::provider('users', function ($app, array $config) {
            return new \Illuminate\Auth\EloquentUserProvider($app['hash'], \App\Models\User::class);
        });
    }
} 