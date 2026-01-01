<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     * Проверяет лимит активных пользователей перед созданием.
     */
    public function creating(User $user): void
    {
        // Проверяем лимит только если пользователь создается с is_active = true
        if ($user->is_active && $user->department_id) {
            $this->checkUserLimit($user->department_id, $user, true);
        }
    }

    /**
     * Handle the User "updating" event.
     * Проверяет лимит активных пользователей при активации.
     */
    public function updating(User $user): void
    {
        // Проверяем лимит только если пользователь активируется (меняется с false на true)
        if ($user->isDirty('is_active') && $user->is_active && !$user->getOriginal('is_active')) {
            if ($user->department_id) {
                $this->checkUserLimit($user->department_id, $user, false);
            }
        }
    }

    /**
     * Проверяет, не превышен ли лимит активных пользователей в отделе.
     * 
     * @param int $departmentId ID отдела
     * @param User $user Пользователь, для которого выполняется проверка
     * @param bool $isNewUser Является ли пользователь новым (создается) или обновляется
     * @throws ValidationException Если лимит превышен
     */
    protected function checkUserLimit(int $departmentId, User $user, bool $isNewUser = false): void
    {
        $department = \App\Models\Department::find($departmentId);
        
        if (!$department) {
            return; // Если отдела нет, пропускаем проверку
        }

        $subscription = $department->getActiveSubscription();
        
        // Если подписки нет или лимит = 0, нельзя создавать активных пользователей
        if (!$subscription) {
            throw ValidationException::withMessages([
                'is_active' => "Невозможно активировать пользователя. У отдела нет активной подписки или не оплачено ни одного пользователя."
            ]);
        }

        // Если лимит = 0, нельзя создавать активных пользователей
        if ($subscription->paid_users_limit <= 0) {
            throw ValidationException::withMessages([
                'is_active' => "Невозможно активировать пользователя. Не оплачено ни одного пользователя в отделе. Оформите подписку для активации пользователей."
            ]);
        }

        $activeUsersCount = $subscription->getActivePaidUsersCount();
        
        if ($isNewUser) {
            // Новый пользователь создается с is_active = true
            // Учитываем его в подсчете, так как он еще не сохранен в БД
            $activeUsersCount++;
        } else {
            // Пользователь обновляется и активируется
            // getOriginal('is_active') вернет старое значение (false)
            // Текущий подсчет уже не включает этого пользователя, так как он еще не активен в БД
            // Поэтому просто проверяем текущий подсчет без добавления
        }

        if ($activeUsersCount > $subscription->paid_users_limit) {
            throw ValidationException::withMessages([
                'is_active' => "Невозможно активировать пользователя. Достигнут лимит оплаченных пользователей ({$subscription->paid_users_limit})."
            ]);
        }
    }
}

