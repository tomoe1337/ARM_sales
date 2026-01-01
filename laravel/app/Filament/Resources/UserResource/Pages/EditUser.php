<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->id !== auth()->id()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->record;
        
        // Проверяем лимит активных пользователей, если активируем (меняем с false на true)
        if (($data['is_active'] ?? false) && !$record->is_active) {
            $department = $record->department;
            if ($department) {
                $subscription = $department->getActiveSubscription();
                
                // Если подписки нет или лимит = 0, нельзя активировать пользователей
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
                
                // Если лимит = 0, нельзя активировать пользователей
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
                if ($activeUsersCount >= $subscription->paid_users_limit) {
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
        if (($data['is_active'] ?? false) && !$record->is_active) {
            $data['activated_at'] = now();
        } elseif (!($data['is_active'] ?? false) && $record->is_active) {
            // Сбрасываем дату активации при деактивации
            $data['activated_at'] = null;
        }
        
        return $data;
    }
}
