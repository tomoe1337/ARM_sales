<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\UserRolesEnum;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        
        // Автоматически привязываем к организации и отделу
        $data['organization_id'] = $user->organization_id;
        $data['department_id'] = $user->department_id;
        
        // По умолчанию создаем менеджера
        if (!isset($data['role'])) {
            $data['role'] = UserRolesEnum::MANAGER->value;
        }
        
        // Активируем пользователя
        $data['is_active'] = true;
        $data['activated_at'] = now();
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
