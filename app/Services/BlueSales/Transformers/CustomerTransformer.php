<?php

namespace App\Services\BlueSales\Transformers;

class CustomerTransformer
{
    public static function fromBlueSalesData(array $data): array
    {
        return [
            'bluesales_id' => (string) ($data['id'] ?? ''),
            'name' => $data['fullName'] ?? '',
            'full_name' => $data['fullName'] ?? '',
            'phone' => $data['phone'] ?? '',
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'country' => isset($data['country']['name']) ? $data['country']['name'] : null,
            'city' => isset($data['city']['name']) ? $data['city']['name'] : null,
            'birth_date' => isset($data['birthday']) ? self::parseDate($data['birthday']) : null,
            'gender' => self::mapGender($data['sex'] ?? null),
            'vk_id' => isset($data['vk']['id']) ? $data['vk']['id'] : null,
            'ok_id' => isset($data['ok']['id']) ? $data['ok']['id'] : null,
            'crm_status' => isset($data['crmStatus']['name']) ? $data['crmStatus']['name'] : null,
            'first_contact_date' => isset($data['firstContactDate']) ? self::parseDate($data['firstContactDate']) : null,
            'next_contact_date' => isset($data['nextContactDate']) ? self::parseDate($data['nextContactDate']) : null,
            'last_contact_date' => isset($data['lastContactDate']) ? self::parseDate($data['lastContactDate']) : null,
            'source' => isset($data['source']['name']) ? $data['source']['name'] : null,
            'sales_channel' => isset($data['salesChannel']['name']) ? $data['salesChannel']['name'] : null,
            'tags' => isset($data['tags']) && is_array($data['tags']) ? implode(',', array_column($data['tags'], 'name')) : null,
            'notes' => ($data['shortNotes'] ?? '') . ' ' . ($data['comments'] ?? ''),
            'additional_contacts' => $data['otherContacts'] ?? null,
            'description' => $data['comments'] ?? null,
            'user_id' => isset($data['manager']['id']) ? self::findManagerUserId($data['manager']) : 1,
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

    private static function mapGender(?string $gender): ?string
    {
        return match($gender) {
            'm' => 'male',
            'f' => 'female',
            default => null
        };
    }

    private static function findManagerUserId(array $manager): int
    {
        // Пока просто возвращаем 1, можно будет доработать поиск по email
        return 1;
    }
}