<?php

use App\Http\Controllers\AnalyticsAI\Ai\GenerateController as AnalyticsGenerateController;
use App\Http\Controllers\AnalyticsAI\Ai\IndexController as AnalyticsIndexController;
use App\Http\Controllers\AnalyticsAI\Ai\ShowController as AnalyticsShowController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Client\CreateController as ClientCreateController;
use App\Http\Controllers\Client\DestroyController as ClientDestroyController;
use App\Http\Controllers\Client\EditController as ClientEditController;
use App\Http\Controllers\Client\IndexController as ClientIndexController;
use App\Http\Controllers\Client\ShowController as ClientShowController;
use App\Http\Controllers\Client\StoreController as ClientStoreController;
use App\Http\Controllers\Client\UpdateController as ClientUpdateController;
use App\Http\Controllers\Dashboard\IndexController as DashboardIndexController;
use App\Http\Controllers\Deal\CreateController as DealCreateController;
use App\Http\Controllers\Deal\EditController as DealEditController;
use App\Http\Controllers\Deal\IndexController as DealIndexController;
use App\Http\Controllers\Deal\Report\DayController as DealReportDayController;
use App\Http\Controllers\Deal\Report\TimeController as DealReportTimeController;
use App\Http\Controllers\Deal\ShowController as DealShowController;
use App\Http\Controllers\Deal\StoreController as DealStoreController;
use App\Http\Controllers\Deal\UpdateController as DealUpdateController;
use App\Http\Controllers\Plan\CreateController as PlanCreateController;
use App\Http\Controllers\Plan\EditController as PlanEditController;
use App\Http\Controllers\Plan\IndexController as PlanIndexController;
use App\Http\Controllers\Plan\ShowController as PlanShowController;
use App\Http\Controllers\Plan\StoreController as PlanStoreController;
use App\Http\Controllers\Plan\UpdateController as PlanUpdateController;
use App\Http\Controllers\Task\CreateController as TaskCreateController;
use App\Http\Controllers\Task\DestroyController as TaskDestroyController;
use App\Http\Controllers\Task\EditController as TaskEditController;
use App\Http\Controllers\Task\IndexController as TaskIndexController;
use App\Http\Controllers\Task\ShowController as TaskShowController;
use App\Http\Controllers\Task\StoreController as TaskStoreController;
use App\Http\Controllers\Task\UpdateController as TaskUpdateController;
use App\Http\Controllers\WorkSession\EndController as WorkSessionEndController;
use App\Http\Controllers\WorkSession\ReportController as WorkSessionReportController;
use App\Http\Controllers\WorkSession\StartController as WorkSessionStartController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
 return auth()->check()
 ? redirect()->route('dashboard')
            : redirect()->route('login');
    })->name('home');

    Route::middleware(['guest'])->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
        Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
    });

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout')
        ->middleware('auth');

    // Защищенные маршруты

    Route::middleware(['auth'])->group(function () {
        // Рабочие сессии
        Route::post('/work-sessions/start', WorkSessionStartController::class)->name('work-sessions.start');
        Route::post('/work-sessions/end', WorkSessionEndController::class)->name('work-sessions.end');
        Route::get('/work-sessions/report', WorkSessionReportController::class)->name('work-sessions.report');
        Route::get('/dashboard', DashboardIndexController::class)->name('dashboard');

    });

    Route::middleware(['auth','working'])->group(function () {

        // Клиенты
        Route::get('/clients', ClientIndexController::class)->name('clients.index');
        Route::get('/clients/create', ClientCreateController::class)->name('clients.create');
        Route::post('/clients', ClientStoreController::class)->name('clients.store');
        Route::get('/clients/{client}', ClientShowController::class)->name('clients.show');
        Route::get('/clients/{client}/edit', ClientEditController::class)->name('clients.edit');
        Route::put('/clients/{client}', ClientUpdateController::class)->name('clients.update');
        Route::delete('/clients/{client}', ClientDestroyController::class)->name('clients.destroy');

        // Задачи
        Route::get('/tasks', TaskIndexController::class)->name('tasks.index');
        Route::get('/tasks/create', TaskCreateController::class)->name('tasks.create');
        Route::post('/tasks', TaskStoreController::class)->name('tasks.store');
        Route::get('/tasks/{task}', TaskShowController::class)->name('tasks.show');
        Route::get('/tasks/{task}/edit', TaskEditController::class)->name('tasks.edit');
        Route::put('/tasks/{task}', TaskUpdateController::class)->name('tasks.update');
        Route::delete('/tasks/{task}', TaskDestroyController::class)->name('tasks.destroy');

        // Сделки
        Route::get('/deals', DealIndexController::class)->name('deals.index');
        Route::get('/deals/create', DealCreateController::class)->name('deals.create');
        Route::post('/deals', DealStoreController::class)->name('deals.store');
        Route::get('/deals/{deal}', DealShowController::class)->name('deals.show');
        Route::get('/deals/report/day', DealReportDayController::class)->name('deals.report.day');
        Route::delete('/deals/{deal}', \App\Http\Controllers\Deal\DestroyController::class)->name('deals.destroy');

        Route::get('/deals/{deal}/edit', DealEditController::class)->name('deals.edit');
        Route::put('/deals/{deal}', DealUpdateController::class)->name('deals.update');

        Route::get('/deals/report/time', DealReportTimeController::class)->name('deals.report.time');
    });

    // Только для руководителей
    Route::middleware(['auth', 'head', 'working'])->group(function () {
        //планы
        Route::get('/plans/create', PlanCreateController::class)->name('plans.create');
        Route::post('/plans', PlanStoreController::class)->name('plans.store');
        Route::get('/plans', PlanIndexController::class)->name('plans.index');
        Route::get('/plans/{plan}', PlanShowController::class)->name('plans.show');
        Route::put('/plans/{user}', PlanUpdateController::class)->name('plans.update');
        Route::get('/plans/{plan}/edit', PlanEditController::class)->name('plans.edit');
        Route::delete('/plans/{plan}', \App\Http\Controllers\Plan\DestroyController::class)->name('plans.destroy');

        //аналитика
        Route::prefix('analytics/ai')->name('analyticsAi.')->group(function () {
            Route::get('/', AnalyticsIndexController::class)->name('index');
            Route::get('/generate', AnalyticsGenerateController::class)->name('generate');
            Route::get('/{analysisAiReport}', AnalyticsShowController::class)->name('report');

        });
    });
