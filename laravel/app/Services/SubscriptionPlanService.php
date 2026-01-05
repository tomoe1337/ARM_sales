<?php

namespace App\Services;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubscriptionPlanService
{
    /**
     * Проверка прав супер-админа
     */
    private function ensureSuperAdmin(User $user): void
    {
        if (!$user->isSuperAdmin()) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'Только супер-администратор может управлять тарифными планами.'
            );
        }
    }

    /**
     * Создать тарифный план
     */
    public function create(array $data, User $user): SubscriptionPlan
    {
        $this->ensureSuperAdmin($user);

        // Генерируем slug из названия, если не указан
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Проверяем уникальность slug
        if (SubscriptionPlan::where('slug', $data['slug'])->exists()) {
            throw ValidationException::withMessages([
                'slug' => 'Тарифный план с таким slug уже существует.'
            ]);
        }

        return SubscriptionPlan::create($data);
    }

    /**
     * Обновить тарифный план
     */
    public function update(SubscriptionPlan $plan, array $data, User $user): SubscriptionPlan
    {
        $this->ensureSuperAdmin($user);

        // Проверяем уникальность slug, если он изменен
        if (isset($data['slug']) && $data['slug'] !== $plan->slug) {
            if (SubscriptionPlan::where('slug', $data['slug'])->where('id', '!=', $plan->id)->exists()) {
                throw ValidationException::withMessages([
                    'slug' => 'Тарифный план с таким slug уже существует.'
                ]);
            }
        }

        $plan->update($data);
        return $plan->fresh();
    }

    /**
     * Удалить тарифный план
     */
    public function delete(SubscriptionPlan $plan, User $user): bool
    {
        $this->ensureSuperAdmin($user);

        // Проверяем, используется ли план в активных подписках
        if ($plan->subscriptions()->where('ends_at', '>', now())->exists()) {
            throw ValidationException::withMessages([
                'plan' => 'Нельзя удалить тарифный план, который используется в активных подписках.'
            ]);
        }

        return $plan->delete();
    }
}

