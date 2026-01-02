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

    public static function toBlueSalesData(\App\Models\Client $client, bool $includeManager = false): array
    {
        // При создании нужно добавить пустой id
        $mapping = [
            'id' => $client->bluesales_id ?? '',
            'fullName' => $client->full_name ?? $client->name ?? '',
            'phone' => $client->phone ?? '',
            'email' => $client->email ?? '',
            'address' => $client->address ?? '',
            'otherContacts' => $client->additional_contacts ?? '',
            'shortNotes' => '',
            'comments' => $client->description ?? '',
        ];

        // Country должен быть объектом
        if ($client->country) {
            $mapping['country'] = ['name' => $client->country];
        } else {
            $mapping['country'] = ['name' => ''];
        }

        // City должен быть объектом
        if ($client->city) {
            $mapping['city'] = ['name' => $client->city];
        } else {
            $mapping['city'] = ['name' => ''];
        }

        // Birthday в формате YYYY-MM-DD или пустая строка
        $mapping['birthday'] = $client->birth_date ? $client->birth_date->format('Y-m-d') : '';

        // Sex: M или F
        if ($client->gender) {
            $mapping['sex'] = $client->gender === 'male' ? 'M' : 'F';
        } else {
            $mapping['sex'] = '';
        }

        // VK должен быть объектом с id и name
        if ($client->vk_id) {
            $mapping['vk'] = ['id' => $client->vk_id, 'name' => ''];
        } else {
            $mapping['vk'] = ['id' => '', 'name' => ''];
        }

        // OK должен быть объектом с id и name
        if ($client->ok_id) {
            $mapping['ok'] = ['id' => $client->ok_id, 'name' => ''];
        } else {
            $mapping['ok'] = ['id' => '', 'name' => ''];
        }

        // CrmStatus должен быть объектом
        if ($client->crm_status) {
            $mapping['crmStatus'] = ['name' => $client->crm_status];
        } else {
            $mapping['crmStatus'] = ['name' => ''];
        }

        // FirstContactDate в формате YYYY-MM-DD или пустая строка
        $mapping['firstContactDate'] = $client->first_contact_date ? $client->first_contact_date->format('Y-m-d') : '';

        // NextContactDate в формате YYYY-MM-DD или пустая строка
        $mapping['nextContactDate'] = $client->next_contact_date ? $client->next_contact_date->format('Y-m-d') : '';

        // Source должен быть объектом с name и autoCreate
        if ($client->source) {
            $mapping['source'] = ['name' => $client->source, 'autoCreate' => true];
        } else {
            $mapping['source'] = ['name' => '', 'autoCreate' => true];
        }

        // SalesChannel должен быть объектом
        if ($client->sales_channel) {
            $mapping['salesChannel'] = ['name' => $client->sales_channel];
        } else {
            $mapping['salesChannel'] = ['name' => ''];
        }

        // Manager должен быть объектом с login и name
        if ($includeManager && $client->user?->email) {
            $mapping['manager'] = ['login' => $client->user->email, 'name' => ''];
        } else {
            $mapping['manager'] = ['login' => '', 'name' => ''];
        }

        return $mapping;
    }
}