<?php

namespace App\Services\BlueSales\Synchronizers;

use App\Models\Order;
use App\Models\Client;
use App\Services\BlueSales\Transformers\OrderTransformer;
use Illuminate\Support\Facades\Log;

class OrderSynchronizer
{
    public function syncOrders(array $orders): array
    {
        Log::info('OrderSynchronizer::syncOrders called', ['orders_count' => count($orders)]);
        $stats = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'errors' => 0, 'skipped' => 0];

        foreach ($orders as $orderData) {
            try {
                $transformedData = OrderTransformer::fromBlueSalesData($orderData);
                
                if (empty($transformedData['bluesales_id'])) {
                    $stats['errors']++;
                    continue;
                }

                // Находим клиента по customer.id из BlueSales
                if (isset($orderData['customer']['id'])) {
                    $client = Client::where('bluesales_id', (string) $orderData['customer']['id'])->first();
                    if ($client) {
                        $transformedData['client_id'] = $client->id;
                        $transformedData['user_id'] = $client->user_id; // Назначаем менеджера клиента
                        // Берем organization_id и department_id из клиента
                        $transformedData['organization_id'] = $client->organization_id;
                        $transformedData['department_id'] = $client->department_id;
                    } else {
                        Log::warning('Client not found for order', [
                            'order_bluesales_id' => $transformedData['bluesales_id'],
                            'customer_bluesales_id' => $orderData['customer']['id']
                        ]);
                        $stats['skipped']++;
                        continue;
                    }
                } else {
                    Log::warning('No customer info in order data', [
                        'order_bluesales_id' => $transformedData['bluesales_id']
                    ]);
                    $stats['skipped']++;
                    continue;
                }

                $order = Order::where('bluesales_id', $transformedData['bluesales_id'])->first();

                if ($order) {
                    // Проверяем, есть ли изменения
                    $hasChanges = false;
                    $changedFields = [];
                    
                    foreach ($transformedData as $key => $value) {
                        if ($key === 'bluesales_last_sync') {
                            continue; // Пропускаем поле синхронизации
                        }
                        
                        $currentValue = $order->getAttribute($key);
                        
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
                        Log::info('Order changes detected', [
                            'bluesales_id' => $transformedData['bluesales_id'],
                            'changed_fields' => $changedFields
                        ]);
                        
                        // Обновляем существующий заказ только при наличии изменений
                        $order->update($transformedData);
                        $stats['updated']++;
                    } else {
                        // Изменений нет, обновляем только время синхронизации
                        $order->update(['bluesales_last_sync' => $transformedData['bluesales_last_sync']]);
                        Log::info('Order unchanged', [
                            'bluesales_id' => $transformedData['bluesales_id']
                        ]);
                        $stats['unchanged']++;
                    }
                } else {
                    // Создаем новый заказ
                    Order::create($transformedData);
                    $stats['created']++;
                }
            } catch (\Exception $e) {
                Log::error('Error syncing order', [
                    'order_data' => $orderData,
                    'error' => $e->getMessage()
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

        // Нормализация чисел (для сумм, скидок и т.д.)
        if (is_numeric($value)) {
            // Приводим к float и обратно к строке для одинакового формата
            return (string) ((float) $value);
        }

        return (string) $value;
    }
}