<?php

namespace App\Jobs;

use App\Models\Department;
use App\Models\DepartmentBluesalesCredential;
use App\Services\BlueSales\BlueSalesApiService;
use App\Services\BlueSales\BlueSalesSyncService;
use App\Services\BlueSales\Synchronizers\CustomerSynchronizer;
use App\Services\BlueSales\Synchronizers\OrderSynchronizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncDepartmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Количество попыток выполнения задачи
     */
    public int $tries = 3;

    /**
     * Таймаут выполнения (15 минут для синхронизации отдела)
     */
    public int $timeout = 900;

    /**
     * Задержка перед повторной попыткой (секунды)
     */
    public int $retryAfter = 60;

    /**
     * ID отдела для синхронизации
     */
    public int $departmentId;

    /**
     * Количество дней назад для синхронизации
     */
    public int $daysBack;

    /**
     * Create a new job instance.
     */
    public function __construct(int $departmentId, int $daysBack = 1)
    {
        $this->departmentId = $departmentId;
        $this->daysBack = $daysBack;
    }

    /**
     * Execute the job.
     * 
     * В рамках одной задачи выполняется:
     * 1. Получение кредов отдела
     * 2. Получение клиентов из BlueSales API
     * 3. Синхронизация клиентов в БД
     * 4. Получение заказов из BlueSales API
     * 5. Синхронизация заказов в БД
     * 6. Обновление статуса синхронизации отдела
     */
    public function handle(): void
    {
        $department = Department::find($this->departmentId);

        if (!$department) {
            Log::error('SyncDepartmentJob: Department not found', [
                'department_id' => $this->departmentId
            ]);
            return;
        }

        $credential = $department->bluesalesCredential;

        if (!$credential || !$credential->isReadyForSync()) {
            Log::warning('SyncDepartmentJob: Department has no valid credentials', [
                'department_id' => $this->departmentId,
                'department_name' => $department->name
            ]);
            return;
        }

        try {
            $login = $credential->login;
            $apiKey = $credential->getDecryptedApiKey();

            if (!$apiKey) {
                $error = 'Не удалось расшифровать API ключ';
                $credential->markSyncError($error);
                Log::error('SyncDepartmentJob: Failed to decrypt API key', [
                    'department_id' => $this->departmentId
                ]);
                return;
            }

            Log::info('SyncDepartmentJob: Starting sync', [
                'department_id' => $this->departmentId,
                'department_name' => $department->name,
                'days_back' => $this->daysBack
            ]);

            // Создаем сервисы для синхронизации
            $apiService = new BlueSalesApiService($login, $apiKey);
            $customerSynchronizer = new CustomerSynchronizer();
            $orderSynchronizer = new OrderSynchronizer();
            
            $syncService = new BlueSalesSyncService(
                $apiService,
                $customerSynchronizer,
                $orderSynchronizer
            );

            // Выполняем синхронизацию
            $result = $syncService->syncDataForPeriod($login, $apiKey, $this->daysBack);

            if ($result['success']) {
                $credential->markSyncSuccess();
                
                Log::info('SyncDepartmentJob: Sync completed successfully', [
                    'department_id' => $this->departmentId,
                    'department_name' => $department->name,
                    'customers' => $result['customers'],
                    'orders' => $result['orders']
                ]);
            } else {
                $error = $result['message'] ?? 'Неизвестная ошибка';
                $credential->markSyncError($error);
                
                Log::error('SyncDepartmentJob: Sync failed', [
                    'department_id' => $this->departmentId,
                    'department_name' => $department->name,
                    'error' => $error
                ]);

                // Пробрасываем исключение для повторной попытки
                throw new \Exception($error);
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $credential->markSyncError($error);
            
            Log::error('SyncDepartmentJob: Exception during sync', [
                'department_id' => $this->departmentId,
                'department_name' => $department->name ?? 'Unknown',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Пробрасываем для повторной попытки
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $department = Department::find($this->departmentId);
        
        Log::error('SyncDepartmentJob: Job failed permanently', [
            'department_id' => $this->departmentId,
            'department_name' => $department?->name ?? 'Unknown',
            'error' => $exception->getMessage(),
        ]);

        if ($department && $department->bluesalesCredential) {
            $department->bluesalesCredential->markSyncError(
                'Критическая ошибка синхронизации: ' . $exception->getMessage()
            );
        }
    }
}

