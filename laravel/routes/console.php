<?php

use App\Console\Commands\ProcessSubscriptions;
use App\Console\Commands\SyncBlueSalesCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Планирование задач
Schedule::command(ProcessSubscriptions::class)->daily();

// Автоматическая синхронизация с BlueSales каждые 5 минут
// Синхронизируются только отделы с включенной синхронизацией
Schedule::command(SyncBlueSalesCommand::class, ['--days' => 1])
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/bluesales-sync.log'))
    ->onFailure(function () {
        Log::error('Ошибка синхронизации с BlueSales');
    });
