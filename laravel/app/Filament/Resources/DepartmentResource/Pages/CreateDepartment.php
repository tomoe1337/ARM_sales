<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use App\Models\DepartmentBluesalesCredential;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Автоматически привязываем к организации пользователя
        $data['organization_id'] = auth()->user()->organization_id;
        
        // Извлекаем данные кредов BlueSales
        $bluesalesData = $data['bluesales_credential'] ?? null;
        unset($data['bluesales_credential']);
        
        return $data;
    }

    protected function afterCreate(): void
    {
        $department = $this->record;
        $bluesalesData = $this->form->getState()['bluesales_credential'] ?? null;

        if ($bluesalesData !== null && 
            (!empty($bluesalesData['login']) || !empty($bluesalesData['api_key']))) {
            // Создаем креды только если указан хотя бы login или api_key
            $createData = [
                'department_id' => $department->id,
                'login' => $bluesalesData['login'] ?? '',
                'api_key' => $bluesalesData['api_key'] ?? '',
                'sync_enabled' => $bluesalesData['sync_enabled'] ?? false,
            ];

            DepartmentBluesalesCredential::create($createData);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}




