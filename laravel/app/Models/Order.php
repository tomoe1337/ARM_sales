<?php

namespace App\Models;

use App\Models\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'bluesales_id',
        'client_id',
        'user_id',
        'deal_id',
        'organization_id',
        'department_id',
        'status',
        'internal_number',
        'external_number',
        'order_date',
        'total_amount',
        'discount',
        'money_discount',
        'delivery_cost',
        'prepay',
        'tracking_number',
        'delivery_service',
        'delivery_info',
        'customer_comments',
        'internal_comments',
        'bluesales_last_sync',
    ];

    protected $casts = [
        'order_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:4',
        'money_discount' => 'decimal:2',
        'delivery_cost' => 'decimal:2',
        'prepay' => 'decimal:2',
        'bluesales_last_sync' => 'datetime',
    ];

    // Связи
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function canView(User $user)
    {
        return $user->isHead() || $this->user_id === $user->id;
    }

    public function canEdit(User $user)
    {
        return $user->isHead() || $this->user_id === $user->id;
    }

    public function canDelete(User $user)
    {
        return $user->isHead() || $this->user_id === $user->id;
    }

    // Global Scope для автоматической фильтрации
    protected static function booted()
    {
        static::addGlobalScope(new OrganizationScope);

        // Автоматическое заполнение organization_id и department_id при создании
        static::creating(function ($order) {
            if (auth()->check()) {
                $user = auth()->user();
                if (!$order->organization_id && $user->organization_id) {
                    $order->organization_id = $user->organization_id;
                }
                if (!$order->department_id && $user->department_id) {
                    $order->department_id = $user->department_id;
                }
            }
        });
    }
}