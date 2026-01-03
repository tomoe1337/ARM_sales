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

    public function createCustomer(array $customerData): ?string
    {
        try {
            Log::info('BlueSales API request to create customer', ['data' => $customerData]);
            
            $url = $this->baseUrl . 'Customers/WebServer.aspx?' . http_build_query([
                'login' => $this->login,
                'password' => $this->apiKey,
                'command' => 'customers.add'
            ]);
            
            Log::info('BlueSales API URL', ['url' => $url]);
            
            $response = Http::timeout(30)
                ->withOptions(['verify' => config('app.env') === 'production'])
                ->post($url, $customerData);

            $status = $response->status();
            $body = $response->body();
            
            // Безопасный парсинг JSON
            $jsonData = null;
            try {
                $jsonData = $response->json();
            } catch (\Exception $e) {
                Log::warning('Failed to parse JSON response', [
                    'body' => $body,
                    'error' => $e->getMessage()
                ]);
            }
            
            Log::info('BlueSales API response', [
                'status' => $status,
                'body' => $body,
                'body_length' => strlen($body),
                'json' => $jsonData,
                'is_successful' => $response->successful(),
                'headers' => $response->headers()
            ]);

            if ($response->successful()) {
                // Проверяем разные возможные форматы ответа
                if (isset($jsonData['id'])) {
                    Log::info('BlueSales customer created', ['bluesales_id' => $jsonData['id']]);
                    return (string) $jsonData['id'];
                }
                
                // Возможно, ID приходит в другом поле или структуре
                if (isset($jsonData['customerId'])) {
                    Log::info('BlueSales customer created', ['bluesales_id' => $jsonData['customerId']]);
                    return (string) $jsonData['customerId'];
                }
                
                // Проверяем, может быть ответ - это просто число (ID)
                if (is_numeric($jsonData)) {
                    Log::info('BlueSales customer created', ['bluesales_id' => $jsonData]);
                    return (string) $jsonData;
                }
                
                // Если ответ - массив с одним элементом
                if (is_array($jsonData) && count($jsonData) === 1 && isset($jsonData[0])) {
                    $firstItem = $jsonData[0];
                    if (isset($firstItem['id'])) {
                        Log::info('BlueSales customer created', ['bluesales_id' => $firstItem['id']]);
                        return (string) $firstItem['id'];
                    }
                }
                
                Log::warning('BlueSales customer creation returned no ID', [
                    'response' => $jsonData,
                    'body' => $body,
                    'status' => $status
                ]);
                return null;
            }

            Log::error('BlueSales API error creating customer', [
                'status' => $status,
                'body' => $body,
                'json' => $jsonData
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('BlueSales API exception creating customer', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    public function updateCustomer(string $bluesalesId, array $customerData): bool
    {
        try {
            Log::info('BlueSales API request to update customer', [
                'bluesales_id' => $bluesalesId,
                'data' => $customerData
            ]);
            
            $url = $this->baseUrl . 'Customers/WebServer.aspx?' . http_build_query([
                'login' => $this->login,
                'password' => $this->apiKey,
                'command' => 'customers.update'
            ]);
            
            $customerData['id'] = (int) $bluesalesId;
            
            $response = Http::timeout(30)
                ->withOptions(['verify' => config('app.env') === 'production'])
                ->post($url, $customerData);

            if ($response->successful()) {
                Log::info('BlueSales customer updated', ['bluesales_id' => $bluesalesId]);
                return true;
            }

            Log::error('BlueSales API error updating customer', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('BlueSales API exception updating customer', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    public function createOrder(array $orderData): ?string
    {
        try {
            Log::info('BlueSales API request to create order', ['data' => $orderData]);
            
            $url = $this->baseUrl . 'Customers/WebServer.aspx?' . http_build_query([
                'login' => $this->login,
                'password' => $this->apiKey,
                'command' => 'orders.add'
            ]);
            
            Log::info('BlueSales API URL for order', ['url' => $url]);
            
            $response = Http::timeout(30)
                ->withOptions(['verify' => config('app.env') === 'production'])
                ->post($url, $orderData);

            $status = $response->status();
            $body = $response->body();
            
            // Безопасный парсинг JSON
            $jsonData = null;
            try {
                $jsonData = $response->json();
            } catch (\Exception $e) {
                Log::warning('Failed to parse JSON response for order', [
                    'body' => $body,
                    'error' => $e->getMessage()
                ]);
            }
            
            Log::info('BlueSales API response for order', [
                'status' => $status,
                'body' => $body,
                'body_length' => strlen($body),
                'json' => $jsonData,
                'is_successful' => $response->successful(),
                'headers' => $response->headers()
            ]);

            if ($response->successful()) {
                // Проверяем разные возможные форматы ответа
                if (isset($jsonData['id'])) {
                    Log::info('BlueSales order created', ['bluesales_id' => $jsonData['id']]);
                    return (string) $jsonData['id'];
                }
                
                if (isset($jsonData['orderId'])) {
                    Log::info('BlueSales order created', ['bluesales_id' => $jsonData['orderId']]);
                    return (string) $jsonData['orderId'];
                }
                
                if (is_numeric($jsonData)) {
                    Log::info('BlueSales order created', ['bluesales_id' => $jsonData]);
                    return (string) $jsonData;
                }
                
                if (is_array($jsonData) && count($jsonData) === 1 && isset($jsonData[0])) {
                    $firstItem = $jsonData[0];
                    if (isset($firstItem['id'])) {
                        Log::info('BlueSales order created', ['bluesales_id' => $firstItem['id']]);
                        return (string) $firstItem['id'];
                    }
                }
                
                Log::warning('BlueSales order creation returned no ID', [
                    'response' => $jsonData,
                    'body' => $body,
                    'status' => $status
                ]);
                return null;
            }

            Log::error('BlueSales API error creating order', [
                'status' => $status,
                'body' => $body,
                'json' => $jsonData
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('BlueSales API exception creating order', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    public function updateOrder(string $bluesalesId, array $orderData): bool
    {
        try {
            Log::info('BlueSales API request to update order', [
                'bluesales_id' => $bluesalesId,
                'data' => $orderData
            ]);
            
            $url = $this->baseUrl . 'Customers/WebServer.aspx?' . http_build_query([
                'login' => $this->login,
                'password' => $this->apiKey,
                'command' => 'orders.update'
            ]);
            
            Log::info('BlueSales API URL for order update', ['url' => $url]);
            
            // Добавляем id заказа в данные
            $orderData['id'] = (int) $bluesalesId;
            
            $response = Http::timeout(30)
                ->withOptions(['verify' => config('app.env') === 'production'])
                ->post($url, $orderData);

            $status = $response->status();
            $body = $response->body();
            
            // Безопасный парсинг JSON
            $jsonData = null;
            try {
                $jsonData = $response->json();
            } catch (\Exception $e) {
                Log::warning('Failed to parse JSON response for order update', [
                    'body' => $body,
                    'error' => $e->getMessage()
                ]);
            }
            
            Log::info('BlueSales API response for order update', [
                'status' => $status,
                'body' => $body,
                'body_length' => strlen($body),
                'json' => $jsonData,
                'is_successful' => $response->successful()
            ]);

            if ($response->successful()) {
                Log::info('BlueSales order updated', ['bluesales_id' => $bluesalesId]);
                return true;
            }

            Log::error('BlueSales API error updating order', [
                'status' => $status,
                'body' => $body,
                'json' => $jsonData
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('BlueSales API exception updating order', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
}