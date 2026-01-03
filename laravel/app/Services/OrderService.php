<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Client;
use App\Models\Department;
use App\Models\User;
use App\Services\BlueSales\BlueSalesApiService;
use App\Services\BlueSales\Transformers\OrderTransformer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * Логика BlueSales: все заказы считаются оплаченными.
     * Так как BlueSales API не предоставляет информацию о типах статусов,
     * заказ создается в момент получения оплаты как факт оплаты.
     * 
     * @param array $data Данные заказа
     * @return Order Созданный заказ
     * @throws \Exception Если синхронизация с BlueSales не удалась
     */
    public function createOrder(array $data): Order
    {
        // Если user_id не указан, берем текущего пользователя
        if (!isset($data['user_id']) && Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        // Заказ всегда привязывается к департаменту текущего пользователя
        // organization_id и department_id берем из текущего пользователя, а не из клиента
        if (Auth::check()) {
            $user = Auth::user();
            // organization_id всегда из текущего пользователя
            $data['organization_id'] = $user->organization_id;
            // department_id всегда из текущего пользователя (заказ создается в департаменте пользователя)
            $data['department_id'] = $user->department_id;
        }

        // Синхронизация выполняется ДО создания в транзакции
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
                    // 2. Сначала синхронизируем с BlueSales
                    $bluesalesId = $this->syncToBlueSalesBeforeCreate($data, $credential);
                    
                    // 3. Если синхронизация не удалась - выбрасываем исключение, транзакция откатится
                    if ($bluesalesId === null) {
                        throw new \Exception('Не удалось создать заказ в BlueSales. Проверьте логи для деталей.');
                    }
                    
                    // 4. Сохраняем bluesales_id в данных перед созданием
                    $data['bluesales_id'] = $bluesalesId;
                    $data['bluesales_last_sync'] = now();
                }
            }
            
            // 5. Только после успешной синхронизации (или если она отключена) создаём заказ в нашей БД
            // Сохраняем позиции заказа, если они есть
            $orderItems = $data['order_items'] ?? [];
            unset($data['order_items']); // Убираем из данных заказа
            
            // Автоматически вычисляем total_amount из позиций заказа
            if (!isset($data['total_amount']) || empty($data['total_amount'])) {
                $totalAmount = 0;
                foreach ($orderItems as $itemData) {
                    $price = (float)($itemData['price'] ?? 0);
                    $quantity = (int)($itemData['quantity'] ?? 1);
                    $totalAmount += $price * $quantity;
                }
                $data['total_amount'] = $totalAmount;
            }
            
            $order = Order::create($data);
            
            // Создаем позиции заказа
            if (!empty($orderItems)) {
                foreach ($orderItems as $itemData) {
                    $itemData['order_id'] = $order->id;
                    // Вычисляем total, если не указан
                    if (!isset($itemData['total'])) {
                        $itemData['total'] = ($itemData['price'] ?? 0) * ($itemData['quantity'] ?? 1);
                    }
                    \App\Models\OrderItem::create($itemData);
                }
            }
            
            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'bluesales_id' => $order->bluesales_id,
                'sync_enabled' => $syncEnabled,
                'items_count' => count($orderItems)
            ]);
            
            return $order;
        });
    }

    /**
     * Синхронизация с BlueSales ДО создания заказа (новый метод с кредами отдела).
     * 
     * @param array $data Данные заказа для синхронизации
     * @param $credential Креды BlueSales отдела
     * @return string|null ID заказа в BlueSales или null при ошибке
     */
    private function syncToBlueSalesBeforeCreate(array $data, $credential): ?string
    {
        try {
            Log::info('Starting BlueSales sync before order creation (department credentials)', [
                'department_id' => $credential->department_id,
                'data' => $data
            ]);
            
            // Проверяем, что есть client_id
            if (!isset($data['client_id'])) {
                Log::error('Cannot sync order to BlueSales: client_id is missing');
                return null;
            }

            // Загружаем клиента с его связями
            $client = Client::with('user')->find($data['client_id']);
            if (!$client) {
                Log::error('Cannot sync order to BlueSales: client not found', ['client_id' => $data['client_id']]);
                return null;
            }

            // Проверяем, что у клиента есть bluesales_id
            if (!$client->bluesales_id) {
                Log::error('Cannot sync order to BlueSales: client has no bluesales_id', ['client_id' => $client->id]);
                return null;
            }

            $apiService = new BlueSalesApiService(
                $credential->login,
                $credential->getDecryptedApiKey()
            );
            
            // Создаем временный объект Order для трансформера
            $tempOrder = new Order($data);
            $tempOrder->setRelation('client', $client);
            if (isset($data['user_id']) && $data['user_id']) {
                $tempOrder->setRelation('user', User::find($data['user_id']));
            } elseif ($client->user) {
                $tempOrder->setRelation('user', $client->user);
            }

            // Загружаем позиции заказа, если они есть
            if (isset($data['order_items']) && is_array($data['order_items'])) {
                $orderItems = collect($data['order_items'])->map(function ($itemData) {
                    return new \App\Models\OrderItem($itemData);
                });
                $tempOrder->setRelation('orderItems', $orderItems);
            } else {
                // Если позиций нет, создаем пустую коллекцию
                $tempOrder->setRelation('orderItems', collect([]));
            }
            
            $bluesalesData = OrderTransformer::toBlueSalesData($tempOrder);
            
            Log::info('BlueSales data prepared for order creation', ['data' => $bluesalesData]);
            
            // Создаем заказ в BlueSales
            $bluesalesId = $apiService->createOrder($bluesalesData);
            
            if ($bluesalesId) {
                Log::info('BlueSales order created successfully', [
                    'bluesales_id' => $bluesalesId,
                    'order_data' => $data,
                    'department_id' => $credential->department_id
                ]);
                return $bluesalesId;
            }
            
            Log::error('BlueSales order creation returned null', [
                'order_data' => $data,
                'bluesales_data' => $bluesalesData,
                'department_id' => $credential->department_id
            ]);
            
            throw new \Exception('Не удалось создать заказ в BlueSales. Проверьте логи для деталей.');
        } catch (\Exception $e) {
            Log::error('Failed to sync order to BlueSales before creation', [
                'department_id' => $credential->department_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_data' => $data
            ]);
            
            throw $e;
        }
    }


    public function updateOrder(Order $order, array $data): bool
    {
        return DB::transaction(function () use ($order, $data) {
            // Сохраняем позиции заказа, если они есть
            $orderItems = $data['order_items'] ?? [];
            unset($data['order_items']); // Убираем из данных заказа
            
            // Автоматически вычисляем total_amount из позиций заказа
            if (!empty($orderItems)) {
                $totalAmount = 0;
                foreach ($orderItems as $itemData) {
                    $price = (float)($itemData['price'] ?? 0);
                    $quantity = (int)($itemData['quantity'] ?? 1);
                    $totalAmount += $price * $quantity;
                }
                $data['total_amount'] = $totalAmount;
            }
            
            // Обновляем заказ
            $order->update($data);
            
            // Обновляем или создаем позиции заказа
            if (!empty($orderItems)) {
                // Получаем существующие ID позиций
                $existingItemIds = collect($orderItems)->pluck('id')->filter()->toArray();
                
                // Удаляем позиции, которых нет в новых данных
                $order->orderItems()->whereNotIn('id', $existingItemIds)->delete();
                
                // Обновляем или создаем позиции
                foreach ($orderItems as $itemData) {
                    if (isset($itemData['id']) && $itemData['id']) {
                        // Обновляем существующую позицию
                        $item = \App\Models\OrderItem::find($itemData['id']);
                        if ($item && $item->order_id === $order->id) {
                            unset($itemData['id']);
                            // Вычисляем total, если не указан
                            if (!isset($itemData['total'])) {
                                $itemData['total'] = ($itemData['price'] ?? 0) * ($itemData['quantity'] ?? 1);
                            }
                            $item->update($itemData);
                        }
                    } else {
                        // Создаем новую позицию
                        $itemData['order_id'] = $order->id;
                        // Вычисляем total, если не указан
                        if (!isset($itemData['total'])) {
                            $itemData['total'] = ($itemData['price'] ?? 0) * ($itemData['quantity'] ?? 1);
                        }
                        \App\Models\OrderItem::create($itemData);
                    }
                }
            } else {
                // Если товаров нет, удаляем все позиции
                $order->orderItems()->delete();
            }
            
            // Синхронизация с BlueSales ПОСЛЕ обновления (если включена и есть bluesales_id)
            $department = $order->department;
            $syncEnabled = false;
            
            if ($department && $department->hasBluesalesSync() && $order->bluesales_id) {
                $credential = $department->bluesalesCredential;
                $syncEnabled = $credential->sync_enabled && $credential->isReadyForSync();
                
                if ($syncEnabled) {
                    // Обновляем заказ из БД, чтобы получить актуальные данные с позициями
                    $order->refresh();
                    $order->load(['client', 'user', 'orderItems']);
                    
                    $syncSuccess = $this->syncToBlueSalesOnUpdate($order, $credential);
                    
                    // Если синхронизация не удалась - выбрасываем исключение, транзакция откатится
                    if ($syncSuccess === false) {
                        throw new \Exception('Не удалось обновить заказ в BlueSales. Проверьте логи для деталей.');
                    }
                    
                    // Обновляем время синхронизации
                    $order->updateQuietly(['bluesales_last_sync' => now()]);
                }
            }
            
            Log::info('Order updated successfully', [
                'order_id' => $order->id,
                'bluesales_id' => $order->bluesales_id,
                'sync_enabled' => $syncEnabled
            ]);
            
            return true;
        });
    }

    /**
     * Синхронизация с BlueSales при обновлении заказа (новый метод с кредами отдела).
     * 
     * @param Order $order Заказ для синхронизации (должен быть загружен с client, user, orderItems)
     * @param $credential Креды BlueSales отдела
     * @return bool true при успехе, false при ошибке
     */
    private function syncToBlueSalesOnUpdate(Order $order, $credential): bool
    {
        try {
            Log::info('Starting BlueSales sync for order update (department credentials)', [
                'order_id' => $order->id,
                'bluesales_id' => $order->bluesales_id,
                'department_id' => $credential->department_id
            ]);
            
            // Проверяем, что у клиента есть bluesales_id
            if (!$order->client || !$order->client->bluesales_id) {
                Log::error('Cannot sync order update to BlueSales: client has no bluesales_id', [
                    'order_id' => $order->id,
                    'client_id' => $order->client_id,
                    'department_id' => $credential->department_id
                ]);
                return false;
            }

            $apiService = new BlueSalesApiService(
                $credential->login,
                $credential->getDecryptedApiKey()
            );
            
            // Преобразуем заказ в формат BlueSales
            $bluesalesData = OrderTransformer::toBlueSalesData($order);
            
            Log::info('BlueSales data prepared for order update', ['data' => $bluesalesData]);
            
            // Обновляем заказ в BlueSales
            $success = $apiService->updateOrder($order->bluesales_id, $bluesalesData);
            
            if ($success) {
                Log::info('BlueSales order updated successfully', [
                    'order_id' => $order->id,
                    'bluesales_id' => $order->bluesales_id,
                    'department_id' => $credential->department_id
                ]);
                return true;
            }
            
            Log::error('BlueSales order update returned false', [
                'order_id' => $order->id,
                'bluesales_id' => $order->bluesales_id,
                'bluesales_data' => $bluesalesData,
                'department_id' => $credential->department_id
            ]);
            
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to sync order update to BlueSales', [
                'department_id' => $credential->department_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $order->id
            ]);
            
            return false;
        }
    }


    public function deleteOrder(Order $order): bool
    {
        return $order->delete();
    }

    public function getDayReportData(\App\Models\User $user): array
    {
        // Логика: все заказы BlueSales считаются оплаченными
        $todayRevenue = Order::where('user_id', $user->id)
            ->whereDate('updated_at', now()->toDateString())
            ->sum('total_amount');

        return compact('todayRevenue');
    }

    public function getMonthReportData(\App\Models\User $user): array
    {
        // Логика: все заказы BlueSales считаются оплаченными
        $monthlyRevenue = Order::where('user_id', $user->id)
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('total_amount');

        return compact('monthlyRevenue');
    }

    public function getTimeReportData(\App\Models\User $user): array
    {
        // The original controller's time report logic was not provided.
        // Implement the logic here based on the required time period.
        return []; // Placeholder
    }
}