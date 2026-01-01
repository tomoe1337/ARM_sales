<?php

namespace App\Services\BlueSales\Transformers;

class OrderTransformer
{
    public static function fromBlueSalesData(array $data): array
    {
        return [
            'bluesales_id' => (string) ($data['id'] ?? ''),
            'client_id' => null, // Will be set separately after client sync
            'user_id' => isset($data['manager']['id']) ? self::findManagerUserId($data['manager']) : 1,
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

    private static function findManagerUserId(array $manager): int
    {
        // Пока просто возвращаем 1, можно будет доработать поиск по email
        return 1;
    }
}