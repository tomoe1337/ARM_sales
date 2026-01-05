<?php

namespace App\Services\BlueSales\Transformers;

class OrderTransformer
{
    public static function fromBlueSalesData(array $data, ?int $departmentId = null): array
    {
        return [
            'bluesales_id' => (string) ($data['id'] ?? ''),
            'client_id' => null, // Will be set separately after client sync
            'user_id' => isset($data['manager']) ? self::findManagerUserId($data['manager'], $departmentId) : null,
            'deal_id' => null,
            'status' => self::mapStatus($data['orderStatus']['name'] ?? 'new'),
            'internal_number' => $data['internalNumber'] ?? null,
            'external_number' => $data['externalNumber'] ?? null,
            'order_date' => isset($data['date']) ? self::parseDate($data['date']) : null,
            'total_amount' => (float) ($data['totalSumWithDelivery'] ?? $data['totalSumMinusDiscount'] ?? 0),
            'discount' => (float) ($data['discount'] ?? 0),
            'money_discount' => (float) ($data['moneyDiscount'] ?? 0),
            'delivery_cost' => (float) ($data['deliveryCost'] ?? 0),
            'prepay' => (float) ($data['prepay'] ?? 0),
            'tracking_number' => $data['trackingNumber'] ?? null,
            'delivery_service' => $data['deliveryService'] ?? null,
            'delivery_info' => isset($data['delivery']) ? json_encode($data['delivery']) : null,
            'customer_comments' => $data['customerComments'] ?? null,
            'internal_comments' => $data['internalComments'] ?? null,
            'bluesales_last_sync' => now(),
        ];
    }

    private static function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }
        
        try {
            // BlueSales использует формат dd.mm.yyyy
            $parsed = \DateTime::createFromFormat('d.m.Y', $date);
            return $parsed ? $parsed->format('Y-m-d') : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private static function mapStatus(string $blueSalesStatus): string
    {
        $statusMap = [
            'Новый' => 'new',
            'Резерв' => 'reserve', 
            'Предзаказ' => 'preorder',
            'Передан на отправку' => 'shipped',
            'Доставлен' => 'delivered',
            'Отменен' => 'cancelled',
            'new' => 'new',
            'reserve' => 'reserve',
            'preorder' => 'preorder',
            'shipped' => 'shipped',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
        ];

        return $statusMap[$blueSalesStatus] ?? 'new';
    }

    private static function findManagerUserId(array $manager, ?int $departmentId = null): ?int
    {
        $email = $manager['login'] ?? $manager['email'] ?? null;
        
        if (!$email || !$departmentId) {
            return null;
        }
        
        $user = \App\Models\User::where('email', $email)
            ->where('department_id', $departmentId)
            ->first();
        
        return $user?->id;
    }

    public static function toBlueSalesData(\App\Models\Order $order): array
    {
        // Проверяем, что у заказа есть клиент с bluesales_id
        if (!$order->client || !$order->client->bluesales_id) {
            throw new \Exception('Заказ не может быть синхронизирован: у клиента отсутствует bluesales_id');
        }

        $mapping = [
            'customer' => [
                'id' => (int) $order->client->bluesales_id
            ],
            'orderStatus' => [
                'name' => self::mapStatusToBlueSales($order->status)
            ],
        ];

        // Менеджер
        if ($order->user?->email) {
            $mapping['manager'] = [
                'login' => $order->user->email
            ];
        }

        // Комментарии менеджера
        if ($order->internal_comments) {
            $mapping['internalComments'] = $order->internal_comments;
        }

        // Позиции заказа (goodsPositions)
        $goodsPositions = [];
        foreach ($order->orderItems as $item) {
            $position = [
                'goods' => [],
                'price' => (float) $item->price,
                'quantity' => (int) $item->quantity,
            ];

            // Товар можно указать по name, marking или id
            if ($item->product_name) {
                $position['goods']['name'] = $item->product_name;
            } elseif ($item->product_marking) {
                $position['goods']['marking'] = $item->product_marking;
            } elseif ($item->product_bluesales_id) {
                $position['goods']['id'] = (int) $item->product_bluesales_id;
            } else {
                // Если нет ни одного идентификатора, используем название
                $position['goods']['name'] = $item->product_name ?? 'Товар без названия';
            }

            $goodsPositions[] = $position;
        }

        // Если нет позиций, добавляем пустую позицию (API может требовать хотя бы одну)
        if (empty($goodsPositions)) {
            $goodsPositions[] = [
                'goods' => ['name' => ''],
                'price' => 0,
                'quantity' => 1,
            ];
        }

        $mapping['goodsPositions'] = $goodsPositions;

        return $mapping;
    }

    private static function mapStatusToBlueSales(string $status): string
    {
        $statusMap = [
            'new' => 'Новый',
            'reserve' => 'Резерв',
            'preorder' => 'Предзаказ',
            'shipped' => 'Передан на отправку',
            'delivered' => 'Доставлен',
            'cancelled' => 'Отменен',
        ];

        return $statusMap[$status] ?? 'Новый';
    }
}