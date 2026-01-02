<?php

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use App\Services\BlueSales\BlueSalesApiService;
use App\Services\BlueSales\Transformers\CustomerTransformer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientService
{
    /**
     * Create a new client.
     *
     * @param array $data The data for the new client.
     * @return Client The created client object.
     * @throws \Exception If BlueSales synchronization fails.
     */
    public function createClient(array $data): Client
    {
        // Получаем пользователя для заполнения полей
        $user = null;
        if (isset($data['user_id'])) {
            $user = User::find($data['user_id']);
        } elseif (Auth::check()) {
            $user = Auth::user();
            $data['user_id'] = $user->id; // Заполняем user_id текущим пользователем
        }
        
        // Если organization_id и department_id не указаны, берем из пользователя
        if ($user && (!isset($data['organization_id']) || !isset($data['department_id']))) {
            $data['organization_id'] = $data['organization_id'] ?? $user->organization_id;
            $data['department_id'] = $data['department_id'] ?? $user->department_id;
        }
        
        // Синхронизация выполняется ДО создания в транзакции
        return DB::transaction(function () use ($data) {
            // 1. Проверяем, включена ли синхронизация
            $syncEnabled = config('bluesales.sync_enabled') 
                && config('bluesales.login') 
                && config('bluesales.api_key');
            
            if ($syncEnabled) {
                // 2. Сначала синхронизируем с BlueSales
                $bluesalesId = $this->syncToBlueSalesBeforeCreate($data);
                
                // 3. Если синхронизация не удалась - выбрасываем исключение, транзакция откатится
                if ($bluesalesId === null) {
                    throw new \Exception('Не удалось создать клиента в BlueSales. Проверьте логи для деталей.');
                }
                
                // 4. Сохраняем bluesales_id в данных перед созданием
                $data['bluesales_id'] = $bluesalesId;
                $data['bluesales_last_sync'] = now();
            }
            
            // 5. Только после успешной синхронизации (или если она отключена) создаём клиента в нашей БД
            $client = Client::create($data);
            
            Log::info('Client created successfully', [
                'client_id' => $client->id,
                'bluesales_id' => $client->bluesales_id,
                'sync_enabled' => $syncEnabled
            ]);
            
            return $client;
        });
    }
    
    /**
     * Синхронизация с BlueSales ДО создания клиента.
     * 
     * @param array $data Данные клиента для синхронизации
     * @return string|null ID клиента в BlueSales или null при ошибке
     */
    private function syncToBlueSalesBeforeCreate(array $data): ?string
    {
        // Проверяем, включена ли синхронизация
        if (!config('bluesales.sync_enabled') || !config('bluesales.login') || !config('bluesales.api_key')) {
            Log::info('BlueSales sync skipped - not enabled or credentials missing', [
                'sync_enabled' => config('bluesales.sync_enabled'),
                'has_login' => !empty(config('bluesales.login')),
                'has_api_key' => !empty(config('bluesales.api_key'))
            ]);
            return null;
        }
        
        try {
            Log::info('Starting BlueSales sync before client creation', ['data' => $data]);
            
            $apiService = new BlueSalesApiService(
                config('bluesales.login'),
                config('bluesales.api_key')
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
                    'client_data' => $data
                ]);
                return $bluesalesId;
            }
            
            Log::error('BlueSales customer creation returned null', [
                'client_data' => $data,
                'bluesales_data' => $bluesalesData
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to sync client to BlueSales before creation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'client_data' => $data
            ]);
            
            return null;
        }
    }
    
    /**
     * Синхронизация с BlueSales для существующего клиента (при обновлении).
     */
    private function syncToBlueSales(Client $client): void
    {
        if (!config('bluesales.sync_enabled') || !config('bluesales.login') || !config('bluesales.api_key')) {
            return;
        }
        
        try {
            $apiService = new BlueSalesApiService(
                config('bluesales.login'),
                config('bluesales.api_key')
            );
            
            // При обновлении передаем manager
            $data = CustomerTransformer::toBlueSalesData($client, true);
            
            if ($client->bluesales_id) {
                // Обновление существующего
                $success = $apiService->updateCustomer($client->bluesales_id, $data);
                if ($success) {
                    $client->updateQuietly(['bluesales_last_sync' => now()]);
                }
            } else {
                // Создание нового (для случаев, когда клиент был создан без синхронизации)
                $bluesalesId = $apiService->createCustomer($data);
                if ($bluesalesId) {
                    $client->updateQuietly([
                        'bluesales_id' => $bluesalesId,
                        'bluesales_last_sync' => now()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync client to BlueSales', [
                'client_id' => $client->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update an existing client.
     *
     * @param Client $client The client to update.
     * @param array $data The data to update the client with.
     * @return Client The updated client object.
     */
    public function updateClient(Client $client, array $data): Client
    {
        $client->update($data);
        
        // Синхронизация с BlueSales
        $this->syncToBlueSales($client);
        
        return $client;
    }

    /**
     * Delete a client.
     *
     * @param Client $client The client to delete.
     * @return bool True if the client was deleted, false otherwise.
     */
    public function deleteClient(Client $client): bool
    {
        return $client->delete();
    }
}