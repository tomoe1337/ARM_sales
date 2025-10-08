<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'description',
        'user_id',
        // BlueSales integration fields
        'bluesales_id',
        'full_name',
        'country',
        'city',
        'birth_date',
        'gender',
        'vk_id',
        'ok_id',
        'crm_status',
        'first_contact_date',
        'next_contact_date',
        'last_contact_date',
        'source',
        'sales_channel',
        'tags',
        'notes',
        'additional_contacts',
        'bluesales_last_sync'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'first_contact_date' => 'datetime',
        'next_contact_date' => 'datetime',
        'last_contact_date' => 'datetime',
        'bluesales_last_sync' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
