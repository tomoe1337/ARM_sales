<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Department;
use App\Models\User;
use App\Services\BlueSales\BlueSalesApiService;
use App\Services\BlueSales\Transformers\CustomerTransformer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientService
{
    /**
     * Создать клиента с синхронизацией в BlueSales
     *
     * @param array $data Данные клиента
     * @return Client Созданный клиент
     * @throws \Exception If BlueSales synchronization fails.
     */
    public function createClient(array $data): Client
    {
        return DB::transaction(function () use ($data) {
            // Получаем отдел из данных или из текущего пользователя
            $departmentId = $data['department_id'] ?? auth()->user()?->department_id;
            $department = $departmentId ? Department::find($departmentId) : null;

            // Проверяем, есть ли креды BlueSales для отдела
            $syncEnabled = false;
            $bluesalesId = null;

            if ($department && $department->hasBluesalesSync()) {
                $credential = $department->bluesalesCredential;
                $syncEnabled = $credential->sync_enabled && $credential->isReadyForSync();

                if ($syncEnabled) {
                    $bluesalesId = $this->syncToBlueSalesBeforeCreate($data, $credential);
                    if ($bluesalesId) {
                        $data['bluesales_id'] = $bluesalesId;
                        $data['bluesales_last_sync'] = now();
                    }
                }
            }

            // Убеждаемся, что organization_id и department_id установлены
            if (!isset($data['organization_id'])) {
                $data['organization_id'] = auth()->user()?->organization_id;
            }
            if (!isset($data['department_id'])) {
                $data['department_id'] = auth()->user()?->department_id;
        }
        
        $client = Client::create($data);
        
            Log::info('Client created', [
                'client_id' => $client->id,
                'bluesales_id' => $bluesalesId,
                'sync_enabled' => $syncEnabled
            ]);
        
        return $client;
        });
    }
    
    /**
     * Синхронизация с BlueSales перед созданием (новый метод с кредами отдела)
     */
    private function syncToBlueSalesBeforeCreate(array $data, $credential): ?string
    {
        try {
            Log::info('Starting BlueSales sync before client creation (department credentials)', [
                'department_id' => $credential->department_id,
                'data' => $data
            ]);
            
            $apiService = new BlueSalesApiService(
                $credential->login,
                $credential->getDecryptedApiKey()
            );
            
            // Создаем временный объект Client для трансформера
            $tempClient = new Client($data);
            if (isset($data['user_id']) && $data['user_id']) {
                $tempClient->setRelation('user', User::find($data['user_id']));
            }
            
            // При создании передаем manager с пустыми значениями (как в примере)
            $bluesalesData = CustomerTransformer::toBlueSalesData($tempClient, false);
            
            Log::info('BlueSales data prepared for creation', ['data' => $bluesalesData]);
            
            // Создаем клиента в BlueSales
            $bluesalesId = $apiService->createCustomer($bluesalesData);
            
                if ($bluesalesId) {
                Log::info('BlueSales customer created successfully', [
                        'bluesales_id' => $bluesalesId,
                    'client_data' => $data,
                    'department_id' => $credential->department_id
                ]);
                return $bluesalesId;
            }
            
            Log::error('BlueSales customer creation returned null', [
                'department_id' => $credential->department_id,
                'client_data' => $data
            ]);
            
            throw new \Exception('Не удалось создать клиента в BlueSales. Проверьте логи для деталей.');
        } catch (\Exception $e) {
            Log::error('BlueSales sync error before client creation', [
                'department_id' => $credential->department_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }


    /**
     * Обновить клиента с синхронизацией в BlueSales
     */
    public function updateClient(Client $client, array $data): Client
    {
        return DB::transaction(function () use ($client, $data) {
            // Получаем отдел клиента
            $department = $client->department;
            
            // Проверяем, есть ли креды BlueSales для отдела
            $syncEnabled = false;
            
            if ($department && $department->hasBluesalesSync()) {
                $credential = $department->bluesalesCredential;
                $syncEnabled = $credential->sync_enabled && $credential->isReadyForSync();

                if ($syncEnabled && $client->bluesales_id) {
                    $this->syncToBlueSales($client, $credential);
                    $data['bluesales_last_sync'] = now();
                }
            }

        $client->update($data);
        
            Log::info('Client updated', [
                'client_id' => $client->id,
                'bluesales_id' => $client->bluesales_id,
                'sync_enabled' => $syncEnabled
            ]);
        
        return $client;
        });
    }

    /**
     * Синхронизация с BlueSales при обновлении (новый метод)
     */
    private function syncToBlueSales(Client $client, $credential): void
    {
        if (!$client->bluesales_id) {
            Log::info('Skipping BlueSales sync - client has no bluesales_id');
            return;
        }

        try {
            Log::info('Starting BlueSales sync for client update (department credentials)', [
                'client_id' => $client->id,
                'bluesales_id' => $client->bluesales_id,
                'department_id' => $credential->department_id
            ]);

            $apiService = new BlueSalesApiService(
                $credential->login,
                $credential->getDecryptedApiKey()
            );

            $bluesalesData = CustomerTransformer::toBlueSalesData($client, true);
            
            $success = $apiService->updateCustomer($client->bluesales_id, $bluesalesData);
            
            if ($success) {
                Log::info('BlueSales customer updated successfully', [
                    'client_id' => $client->id,
                    'bluesales_id' => $client->bluesales_id,
                    'department_id' => $credential->department_id
                ]);
                $client->updateQuietly(['bluesales_last_sync' => now()]);
            } else {
                Log::error('BlueSales customer update failed', [
                    'client_id' => $client->id,
                    'bluesales_id' => $client->bluesales_id,
                    'department_id' => $credential->department_id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('BlueSales sync error for client update', [
                'client_id' => $client->id,
                'bluesales_id' => $client->bluesales_id,
                'department_id' => $credential->department_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }


    /**
     * Удалить клиента
     * 
     * @param Client $client Клиент для удаления
     * @return bool
     */
    public function deleteClient(Client $client): bool
    {
        return DB::transaction(function () use ($client) {
            // Сохраняем данные перед удалением
            $clientId = $client->id;
            $bluesalesId = $client->bluesales_id;
            $department = $client->department;
            $departmentId = $department?->id;

            // Удаляем клиента из БД
            $deleted = $client->delete();

            if ($deleted) {
                if ($bluesalesId && $department && $department->hasBluesalesSync()) {
                    // Пытаемся удалить из BlueSales (если API поддерживает)
                    // Пока просто логируем, так как в BlueSales API нет метода удаления
                    Log::info('Client deleted (BlueSales sync not implemented for deletion)', [
                        'client_id' => $clientId,
                        'bluesales_id' => $bluesalesId,
                        'department_id' => $departmentId
                    ]);
                }

                Log::info('Client deleted', [
                    'client_id' => $clientId,
                    'bluesales_id' => $bluesalesId
                ]);
            }

            return $deleted;
        });
    }
}
