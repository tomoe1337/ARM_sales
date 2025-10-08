<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'bluesales_id',
        'client_id',
        'user_id',
        'deal_id',
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

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
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
}