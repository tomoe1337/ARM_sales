<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_per_user',
        'ai_analytics_enabled',
        'crm_sync_enabled',
        'is_active',
    ];

    protected $casts = [
        'price_per_user' => 'decimal:2',
        'ai_analytics_enabled' => 'boolean',
        'crm_sync_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Получить стандартный тариф
     */
    public static function getStandard(): self
    {
        return static::where('slug', 'standard')->firstOrCreate(
            ['slug' => 'standard'],
            [
                'name' => 'Стандарт',
                'description' => 'Стандартный тарифный план',
                'price_per_user' => 500,
                'ai_analytics_enabled' => true,
                'crm_sync_enabled' => true,
                'is_active' => true,
            ]
        );
    }

    /**
     * Связь с подписками
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}

