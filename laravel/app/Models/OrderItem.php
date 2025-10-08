<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_name',
        'product_marking',
        'product_bluesales_id',
        'size',
        'price',
        'quantity',
        'total',
        'custom_fields',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'custom_fields' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}