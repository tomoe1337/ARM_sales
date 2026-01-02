<?php

namespace App\Observers;

use App\Models\Client;
use App\Services\BlueSales\BlueSalesApiService;
use App\Services\BlueSales\Transformers\CustomerTransformer;
use Illuminate\Support\Facades\Log;

class ClientObserver
{
    public function created(Client $client): void
    {
        // Синхронизация при создании теперь выполняется в ClientService::createClient()
        // ДО сохранения клиента в БД, поэтому здесь пропускаем синхронизацию
        // чтобы избежать дублирования и конфликтов
        
        Log::info('ClientObserver::created called - sync skipped (handled in ClientService)', [
            'client_id' => $client->id,
            'client_name' => $client->name,
            'bluesales_id' => $client->bluesales_id
        ]);
    }

    public function updated(Client $client): void
    {
        if (!$this->shouldSync() || !$client->bluesales_id) {
            return;
        }

        try {
            $apiService = $this->getApiService();
            $data = CustomerTransformer::toBlueSalesData($client);
            
            $success = $apiService->updateCustomer($client->bluesales_id, $data);
            
            if ($success) {
                $client->updateQuietly(['bluesales_last_sync' => now()]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync client update to BlueSales', [
                'client_id' => $client->id,
                'bluesales_id' => $client->bluesales_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function shouldSync(): bool
    {
        return config('bluesales.sync_enabled', false) 
            && config('bluesales.login') 
            && config('bluesales.api_key');
    }

    private function getApiService(): BlueSalesApiService
    {
        return new BlueSalesApiService(
            config('bluesales.login'),
            config('bluesales.api_key')
        );
    }
}

