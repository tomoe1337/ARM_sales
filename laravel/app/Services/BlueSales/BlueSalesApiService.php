<?php

namespace App\Services\BlueSales;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BlueSalesApiService
{
    private string $login;
    private string $apiKey;
    private string $baseUrl = 'https://bluesales.ru/app/';

    public function __construct(string $login, string $apiKey)
    {
        $this->login = $login;
        $this->apiKey = $apiKey; // Используется как пароль
    }

    public function getCustomers(string $startDate, string $endDate, int $limit = 500, int $offset = 0): array
    {
        try {
            Log::info('BlueSales API request for customers', [
                'url' => $this->baseUrl . 'Customers/WebServer.aspx',
                'period' => $startDate . ' - ' . $endDate
            ]);
            
            // BlueSales использует GET параметры для авторизации и POST тело
            $url = $this->baseUrl . 'Customers/WebServer.aspx?' . http_build_query([
                'login' => $this->login,
                'password' => $this->apiKey,
                'command' => 'customers.get'
            ]);
            
            $response = Http::timeout(30)
                ->withOptions([
                    'verify' => config('app.env') === 'production',
                ])
                ->post($url, [
                    'firstContactDateFrom' => $startDate,
                    'firstContactDateTill' => $endDate,
                    'ids' => null,
                    'vkIds' => null,
                    'pageSize' => (string) $limit,
                    'startRowNumber' => (string) $offset,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // BlueSales возвращает объект с полем customers
                if (isset($data['customers']) && is_array($data['customers'])) {
                    Log::info('BlueSales API success for customers', [
                        'count' => count($data['customers']),
                        'total_count' => $data['count'] ?? 0
                    ]);
                    
                    return $data['customers'];
                }
                
                Log::warning('BlueSales API returned unexpected structure for customers', [
                    'data_structure' => array_keys($data ?? []),
                    'data' => $data
                ]);
                
                return [];
            }

            Log::error('BlueSales API error for customers', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('BlueSales API exception for customers', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [];
        }
    }

    public function getOrders(string $startDate, string $endDate, int $limit = 500, int $offset = 0): array
    {
        try {
            Log::info('BlueSales API request for orders', [
                'url' => $this->baseUrl . 'Customers/WebServer.aspx',
                'period' => $startDate . ' - ' . $endDate
            ]);
            
            // По документации заказы используют тот же URL что и клиенты
            $url = $this->baseUrl . 'Customers/WebServer.aspx?' . http_build_query([
                'login' => $this->login,
                'password' => $this->apiKey,
                'command' => 'orders.get'
            ]);
            
            $response = Http::timeout(30)
                ->withOptions([
                    'verify' => config('app.env') === 'production',
                ])
                ->post($url, [
                    'dateFrom' => $startDate,
                    'dateTill' => $endDate,
                    'ids' => null,
                    'customerIds' => null,
                    'pageSize' => (string) $limit,
                    'startRowNumber' => (string) $offset,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // BlueSales возвращает объект с полем orders
                if (isset($data['orders']) && is_array($data['orders'])) {
                    Log::info('BlueSales API success for orders', [
                        'count' => count($data['orders']),
                        'total_count' => $data['count'] ?? 0
                    ]);
                    
                    return $data['orders'];
                }
                
                Log::warning('BlueSales API returned unexpected structure for orders', [
                    'data_structure' => array_keys($data ?? []),
                    'data' => $data
                ]);
                
                return [];
            }

            Log::error('BlueSales API error for orders', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('BlueSales API exception for orders', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [];
        }
    }
}