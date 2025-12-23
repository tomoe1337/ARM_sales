<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessSubscriptions extends Command
{
    protected $signature = 'subscriptions:process';
    protected $description = 'Обрабатывает подписки (зарезервировано для будущего)';

    public function handle()
    {
        // Пока не используется, т.к. статусы определяются по датам
        $this->info('Обработка подписок завершена.');
        return Command::SUCCESS;
    }
}
