<?php

namespace App\Services\BlueSales\Synchronizers;

use App\Models\Client;
use App\Services\BlueSales\Transformers\CustomerTransformer;
use Illuminate\Support\Facades\Log;

class CustomerSynchronizer
{
    public function syncCustomers(array $customers): array
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
                
                $transformedData = CustomerTransformer::fromBlueSalesData($customerData);
                
                if (empty($transformedData['bluesales_id'])) {
                    $stats['errors']++;
                    continue;
                }

                $client = Client::where('bluesales_id', $transformedData['bluesales_id'])->first();

                if ($client) {
                    // Проверяем, есть ли изменения
                    $hasChanges = false;
                    $changedFields = [];
                    
                    foreach ($transformedData as $key => $value) {
                        if ($key === 'bluesales_last_sync') {
                            continue; // Пропускаем поле синхронизации
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
                        
                        // Обновляем существующего клиента только при наличии изменений
                        $client->update($transformedData);
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
                    // Назначаем первому доступному пользователю, если не указан
                    if (!isset($transformedData['user_id'])) {
                        $transformedData['user_id'] = 1; // Default user
                    }
                    
                    Client::create($transformedData);
                    $stats['created']++;
                }
            } catch (\Exception $e) {
                Log::error('Error syncing customer', [
                    'customer_data' => is_array($customerData) ? $customerData : ['invalid_data' => $customerData],
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
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