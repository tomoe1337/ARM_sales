<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subscription extends Model
{
    protected $fillable = [
        'department_id',
        'organization_id',
        'subscription_plan_id',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'paid_users_limit',
        'monthly_price',
        'auto_renew',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'paid_users_limit' => 'integer',
        'monthly_price' => 'decimal:2',
        'auto_renew' => 'boolean',
    ];

    // Связи
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'payment_subscriptions')
            ->withPivot('amount')
            ->withTimestamps();
    }

    public function isActive(): bool
    {
        return $this->ends_at->isFuture();
    }

    public function isTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->ends_at->isPast();
    }


    /**
     * Получить количество активных платных пользователей
     */
    public function getActivePaidUsersCount(): int
    {
        return $this->department->users()
            ->where('is_paid', true)
            ->count();
    }
}

