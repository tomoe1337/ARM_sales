<?php

namespace App\Console\Commands;

use App\Jobs\SyncDepartmentJob;
use App\Models\Department;
use App\Models\DepartmentBluesalesCredential;
use Illuminate\Console\Command;

class SyncBlueSalesCommand extends Command
{
    protected $signature = 'bluesales:sync 
                            {--department= : ID конкретного отдела для синхронизации}
                            {--days=1 : Количество дней назад для синхронизации}';

    protected $description = 'Автоматическая синхронизация данных с BlueSales для всех отделов или конкретного отдела';

    public function handle(): int
    {
        $departmentId = $this->option('department');
        $daysBack = (int) $this->option('days');

        if ($departmentId) {
            // Синхронизация конкретного отдела (синхронно, для отладки)
            $department = Department::find($departmentId);
            if (!$department) {
                $this->error("Отдел с ID {$departmentId} не найден");
                return Command::FAILURE;
            }

            $this->info("Диспатчим Job для отдела: {$department->name}");
            SyncDepartmentJob::dispatch($department->id, $daysBack);
            $this->info("Job добавлен в очередь");
            
            return Command::SUCCESS;
        }

        // Синхронизация всех отделов с настроенными кредами
        $credentials = DepartmentBluesalesCredential::where('sync_enabled', true)
            ->with('department')
            ->get();

        if ($credentials->isEmpty()) {
            $this->info('Нет отделов с включенной синхронизацией BlueSales');
            return Command::SUCCESS;
        }

        $this->info("Найдено отделов для синхронизации: {$credentials->count()}");
        $this->info("Диспатчим Jobs в очередь...");

        $dispatchedCount = 0;

        foreach ($credentials as $credential) {
            if (!$credential->department) {
                continue;
            }

            // Определяем период синхронизации:
            // - Если это первая синхронизация (last_sync_at == null) → 30 дней (загружаем историю)
            // - Если уже была синхронизация → используем переданный daysBack (обычно 1 день)
            $syncDays = $credential->last_sync_at === null ? 30 : $daysBack;
            
            $this->line("  → Отдел: {$credential->department->name} (ID: {$credential->department_id}) - " . 
                       ($syncDays === 30 ? "первая синхронизация (30 дней)" : "{$syncDays} день(дней)"));
            
            // Диспатчим Job для каждого отдела
            SyncDepartmentJob::dispatch($credential->department_id, $syncDays);
            $dispatchedCount++;
        }

        $this->info("✓ Добавлено Jobs в очередь: {$dispatchedCount}");
        $this->info("Задачи будут обработаны воркерами очереди параллельно");

        return Command::SUCCESS;
    }

}

