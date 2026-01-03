<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use App\Models\DepartmentBluesalesCredential;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditDepartment extends EditRecord
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Загружаем данные кредов BlueSales при открытии формы
        $department = $this->record;
        if ($department && $department->bluesalesCredential) {
            $credential = $department->bluesalesCredential;
            $data['bluesales_credential'] = [
                'login' => $credential->login,
                'api_key' => '', // Не показываем зашифрованный ключ
                'sync_enabled' => $credential->sync_enabled,
            ];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Извлекаем данные кредов BlueSales
        $bluesalesData = $data['bluesales_credential'] ?? null;
        unset($data['bluesales_credential']);

        return $data;
    }

    protected function afterSave(): void
    {
        $department = $this->record;
        $bluesalesData = $this->form->getState()['bluesales_credential'] ?? null;

        if ($bluesalesData !== null) {
            // Обновляем или создаем креды
            $credential = $department->bluesalesCredential;

            if ($credential) {
                // Обновляем существующие креды
                $updateData = [
                    'sync_enabled' => $bluesalesData['sync_enabled'] ?? false,
                ];

                // Обновляем login только если он указан
                if (!empty($bluesalesData['login'])) {
                    $updateData['login'] = $bluesalesData['login'];
                }

                // Обновляем API ключ только если он указан (не пустой)
                if (isset($bluesalesData['api_key']) && !empty($bluesalesData['api_key'])) {
                    $updateData['api_key'] = $bluesalesData['api_key'];
                }

                $credential->update($updateData);
            } else {
                // Создаем новые креды только если указан хотя бы login или api_key
                // (api_key теперь может быть null, но валидация не даст включить sync_enabled без кредов)
                if (!empty($bluesalesData['login']) || !empty($bluesalesData['api_key'])) {
                    $createData = [
                        'department_id' => $department->id,
                        'login' => $bluesalesData['login'] ?? '',
                        'api_key' => $bluesalesData['api_key'] ?? null,
                        'sync_enabled' => $bluesalesData['sync_enabled'] ?? false,
                    ];

                    DepartmentBluesalesCredential::create($createData);
                }
            }
        }
    }
}
