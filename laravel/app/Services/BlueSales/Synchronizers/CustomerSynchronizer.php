<?php

namespace App\Services\BlueSales\Synchronizers;

use App\Models\Client;
use App\Models\User;
use App\Services\BlueSales\Transformers\CustomerTransformer;
use Illuminate\Support\Facades\Log;

class CustomerSynchronizer
{
    public function syncCustomers(array $customers, int $organizationId, int $departmentId): array
    {
        Log::info('CustomerSynchronizer::syncCustomers called', ['customers_count' => count($customers)]);
        $stats = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'errors' => 0];

        foreach ($customers as $customerData) {
            try {
                // Проверяем, что данные клиента - это массив
                if (!is_array($customerData)) {
                    Log::warning('Customer data is not an array', [
                        'data_type' => gettype($customerData),
                        'data' => $customerData
                    ]);
                    $stats['errors']++;
                    continue;
                }
                
                $transformedData = CustomerTransformer::fromBlueSalesData($customerData, $departmentId);
                
                if (empty($transformedData['bluesales_id'])) {
                    $stats['errors']++;
                    continue;
                }

                $client = Client::where('bluesales_id', $transformedData['bluesales_id'])->first();

                if ($client) {
                    // Исправляем старые данные с NULL organization_id/department_id
                    if (!$client->organization_id || !$client->department_id) {
                        $client->update([
                            'organization_id' => $organizationId,
                            'department_id' => $departmentId
                        ]);
                    }
                    
                    // Проверяем, есть ли изменения
                    $hasChanges = false;
                    $changedFields = [];
                    
                    foreach ($transformedData as $key => $value) {
                        // Пропускаем служебные поля
                        if (in_array($key, ['bluesales_last_sync', 'organization_id', 'department_id'])) {
                            continue;
                        }
                        
                        $currentValue = $client->getAttribute($key);
                        
                        // Нормализуем значения для корректного сравнения
                        $currentNormalized = $this->normalizeValue($currentValue, $key);
                        $newNormalized = $this->normalizeValue($value, $key);
                        
                        if ($currentNormalized !== $newNormalized) {
                            $hasChanges = true;
                            $changedFields[] = [
                                'field' => $key,
                                'old' => $currentNormalized,
                                'new' => $newNormalized
                            ];
                        }
                    }
                    
                    if ($hasChanges) {
                        // Логируем изменения для отладки
                        Log::info('Customer changes detected', [
                            'bluesales_id' => $transformedData['bluesales_id'],
                            'changed_fields' => $changedFields
                        ]);
                        
                        // Исключаем служебные поля из обновления
                        $updateData = array_diff_key($transformedData, array_flip(['organization_id', 'department_id', 'bluesales_last_sync']));
                        $client->update($updateData);
                        $stats['updated']++;
                    } else {
                        // Изменений нет, обновляем только время синхронизации
                        $client->update(['bluesales_last_sync' => $transformedData['bluesales_last_sync']]);
                        Log::info('Customer unchanged', [
                            'bluesales_id' => $transformedData['bluesales_id']
                        ]);
                        $stats['unchanged']++;
                    }
                } else {
                    // Создаем нового клиента
                    Log::info('Creating new client - before setting org/dept', [
                        'bluesales_id' => $transformedData['bluesales_id'] ?? null,
                        'organization_id_param' => $organizationId,
                        'department_id_param' => $departmentId,
                        'transformed_data_keys' => array_keys($transformedData),
                        'has_org_in_transformed' => isset($transformedData['organization_id']),
                        'has_dept_in_transformed' => isset($transformedData['department_id']),
                    ]);
                    
                    $transformedData['organization_id'] = $organizationId;
                    $transformedData['department_id'] = $departmentId;
                    
                    Log::info('Creating new client - after setting org/dept', [
                        'bluesales_id' => $transformedData['bluesales_id'] ?? null,
                        'organization_id' => $transformedData['organization_id'] ?? null,
                        'department_id' => $transformedData['department_id'] ?? null,
                        'user_id' => $transformedData['user_id'] ?? null,
                        'all_keys' => array_keys($transformedData),
                    ]);
                    
                    try {
                        $client = Client::create($transformedData);
                        Log::info('Client created successfully', [
                            'client_id' => $client->id,
                            'organization_id' => $client->organization_id,
                            'department_id' => $client->department_id,
                        ]);
                        $stats['created']++;
                    } catch (\Exception $createException) {
                        Log::error('Failed to create client', [
                            'bluesales_id' => $transformedData['bluesales_id'] ?? null,
                            'organization_id' => $transformedData['organization_id'] ?? null,
                            'department_id' => $transformedData['department_id'] ?? null,
                            'error' => $createException->getMessage(),
                            'error_trace' => $createException->getTraceAsString(),
                            'transformed_data' => $transformedData,
                        ]);
                        throw $createException;
                    }
                }
            } catch (\Exception $e) {
                $logData = [
                    'customer_data' => is_array($customerData) ? $customerData : ['invalid_data' => $customerData],
                    'organization_id' => $organizationId ?? null,
                    'department_id' => $departmentId ?? null,
                    'error' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ];
                
                if (isset($transformedData)) {
                    $logData['bluesales_id'] = $transformedData['bluesales_id'] ?? null;
                    $logData['transformed_data'] = $transformedData;
                }
                
                Log::error('Error syncing customer', $logData);
                $stats['errors']++;
            }
        }

        return $stats;
    }

    /**
     * Нормализует значения для корректного сравнения
     */
    private function normalizeValue($value, string $fieldName): string
    {
        if ($value === null) {
            return '';
        }

        // Нормализация дат
        if (str_contains($fieldName, 'date') || str_contains($fieldName, '_date')) {
            if ($value instanceof \DateTime || $value instanceof \Carbon\Carbon) {
                return $value->format('Y-m-d');
            }
            if (is_string($value) && strlen($value) > 10) {
                // Обрезаем время, оставляем только дату
                return substr($value, 0, 10);
            }
        }

        // Нормализация чисел
        if (is_numeric($value)) {
            return (string) ((float) $value);
        }

        return (string) $value;
    }
}