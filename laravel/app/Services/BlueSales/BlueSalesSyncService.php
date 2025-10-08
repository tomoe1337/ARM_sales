<?php

namespace App\Services\BlueSales;

use App\Services\BlueSales\BlueSalesApiService;
use App\Services\BlueSales\Synchronizers\CustomerSynchronizer;
use App\Services\BlueSales\Synchronizers\OrderSynchronizer;
use Carbon\Carbon;

class BlueSalesSyncService
{
    private BlueSalesApiService $apiService;
    private CustomerSynchronizer $customerSynchronizer;
    private OrderSynchronizer $orderSynchronizer;

    public function __construct(
        BlueSalesApiService $apiService,
        CustomerSynchronizer $customerSynchronizer,
        OrderSynchronizer $orderSynchronizer
    ) {
        $this->apiService = $apiService;
        $this->customerSynchronizer = $customerSynchronizer;
        $this->orderSynchronizer = $orderSynchronizer;
    }

    public function syncDataForPeriod(string $login, string $apiKey, int $daysBack = 30): array
    {
        // Создаем новый экземпляр API сервиса с ключом
        $apiService = new BlueSalesApiService($login, $apiKey);
        
        // BlueSales считает дату "до" невключительной, поэтому добавляем 1 день
        $endDate = Carbon::now()->addDay()->format('Y-m-d');
        $startDate = Carbon::now()->subDays($daysBack)->format('Y-m-d');

        $result = [
            'success' => true,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'customers' => ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'errors' => 0],
            'orders' => ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'errors' => 0, 'skipped' => 0],
            'message' => ''
        ];

        try {
            // Синхронизируем клиентов
            $customers = $apiService->getCustomers($startDate, $endDate);
            if (!empty($customers)) {
                $result['customers'] = $this->customerSynchronizer->syncCustomers($customers);
            }

            // Синхронизируем заказы
            $orders = $apiService->getOrders($startDate, $endDate);
            if (!empty($orders)) {
                $result['orders'] = $this->orderSynchronizer->syncOrders($orders);
            }

            $result['message'] = 'Синхронизация завершена успешно';
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = 'Ошибка синхронизации: ' . $e->getMessage();
        }

        return $result;
    }
}