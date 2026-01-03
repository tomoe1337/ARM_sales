<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'head_id',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    // Связи
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Связь с кредами BlueSales
     */
    public function bluesalesCredential(): HasOne
    {
        return $this->hasOne(DepartmentBluesalesCredential::class);
    }

    /**
     * Получить креды BlueSales для отдела
     */
    public function getBluesalesCredential(): ?DepartmentBluesalesCredential
    {
        return $this->bluesalesCredential;
    }

    /**
     * Проверить, настроена ли синхронизация с BlueSales
     */
    public function hasBluesalesSync(): bool
    {
        $credential = $this->bluesalesCredential;
        return $credential && $credential->isReadyForSync();
    }

    /**
     * Получить активную подписку отдела
     * Активная подписка - та, которая уже началась (starts_at <= now) и еще не истекла (ends_at > now)
     */
    public function getActiveSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now())
            ->orderBy('ends_at', 'desc')
            ->first();
    }

    /**
     * Получить подписку отдела (для обратной совместимости)
     * @deprecated Используйте getActiveSubscription() вместо этого метода
     */
    public function subscription(): ?Subscription
    {
        return $this->getActiveSubscription();
    }

    public function hasSubscription(): bool
    {
        return $this->getActiveSubscription() !== null;
    }

    /**
     * Автоматическое обновление флага is_single_department в организации
     * и создание подписки при создании отдела
     */
    protected static function booted()
    {
        static::created(function ($department) {
            if (!$department->getActiveSubscription() && !$department->organization->hasUsedTrial()) {
                $plan = \App\Models\SubscriptionPlan::getStandard();
                \App\Models\Subscription::create([
                    'department_id' => $department->id,
                    'organization_id' => $department->organization_id,
                    'subscription_plan_id' => $plan->id,
                    'starts_at' => now(),
                    'ends_at' => now()->addDays(14),
                    'trial_ends_at' => now()->addDays(14),
                    'paid_users_limit' => 1, // Пробный период дает 1 оплаченный слот для создателя отдела
                    'monthly_price' => $plan->price_per_user, // Цена за 1 пользователя
                    'auto_renew' => false,
                ]);
                $department->organization->update(['trial_used_at' => now()]);
            }
        });

        static::saved(function ($department) {
            if ($department->organization) {
                $department->organization->checkIfSingleDepartment();
            }
        });

        static::deleted(function ($department) {
            if ($department->organization) {
                $department->organization->checkIfSingleDepartment();
            }
        });
    }
}
