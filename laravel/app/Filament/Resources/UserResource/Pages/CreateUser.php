<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        
        // Автоматически привязываем к организации и отделу
        $data['organization_id'] = $user->organization_id;
        $data['department_id'] = $user->department_id;
        
        // Убираем прямое заполнение role - будет назначено через Spatie после создания
        // if (!isset($data['role'])) {
        //     $data['role'] = UserRolesEnum::MANAGER->value;  // ❌ УБРАНО
        // }
        
        // Проверяем лимит активных пользователей, если активируем
        if (($data['is_active'] ?? false)) {
            $department = \App\Models\Department::find($data['department_id']);
            if ($department) {
                $subscription = $department->getActiveSubscription();
                
                // Если подписки нет или лимит = 0, нельзя создавать активных пользователей
                if (!$subscription) {
                    $message = "Невозможно активировать пользователя. У отдела нет активной подписки или не оплачено ни одного пользователя.";
                    Notification::make()
                        ->danger()
                        ->title('Ошибка активации')
                        ->body($message)
                        ->send();
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'is_active' => $message
                    ]);
                }
                
                // Если лимит = 0, нельзя создавать активных пользователей
                if ($subscription->paid_users_limit <= 0) {
                    $message = "Невозможно активировать пользователя. Не оплачено ни одного пользователя в отделе. Оформите подписку для активации пользователей.";
                    Notification::make()
                        ->danger()
                        ->title('Ошибка активации')
                        ->body($message)
                        ->send();
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'is_active' => $message
                    ]);
                }
                
                $activeUsersCount = $subscription->getActivePaidUsersCount();
                // Учитываем нового пользователя в подсчете
                $activeUsersCount++;
                
                if ($activeUsersCount > $subscription->paid_users_limit) {
                    $message = "Невозможно активировать пользователя. Достигнут лимит оплаченных пользователей ({$subscription->paid_users_limit}).";
                    Notification::make()
                        ->danger()
                        ->title('Ошибка активации')
                        ->body($message)
                        ->send();
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'is_active' => $message
                    ]);
                }
            }
        }
        
        // Устанавливаем дату активации, если активируем
        if ($data['is_active'] ?? false) {
            $data['activated_at'] = now();
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Назначаем роль через Spatie после создания
        $role = $this->form->getState()['role'] ?? 'manager';
        $this->record->assignRole($role);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
