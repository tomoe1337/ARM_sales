<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkSessionController;
use App\Http\Controllers\PlanController;

Route::middleware(['web'])->group(function () {
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
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Рабочие сессии
        Route::post('/work-sessions/start', [WorkSessionController::class, 'start'])->name('work-sessions.start');
        Route::post('/work-sessions/end', [WorkSessionController::class, 'end'])->name('work-sessions.end');
        Route::get('/work-sessions/report', [WorkSessionController::class, 'report'])->name('work-sessions.report');

        // Клиенты
        Route::resource('clients', ClientController::class);
        Route::get('clients/{client}/info', [ClientController::class, 'info'])->name('clients.info');

        // Задачи
        Route::resource('tasks', TaskController::class);
        Route::get('tasks/{task}/info', [TaskController::class, 'info'])->name('tasks.info');
        Route::get('tasks/plan', [TaskController::class, 'plan'])->name('tasks.plan');

        // Сделки
        Route::resource('deals', DealController::class);
        Route::get('deals/report/day', [DealController::class, 'dayReport'])->name('deals.report.day');
        Route::get('deals/report/month', [DealController::class, 'monthReport'])->name('deals.report.month');
        Route::get('deals/report/time', [DealController::class, 'timeReport'])->name('deals.report.time');
    });

    // Планы (только для руководителей)
    Route::middleware(['auth', 'head'])->group(function () {
        Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
        Route::put('/plans/{user}', [PlanController::class, 'update'])->name('plans.update');
    });
});
