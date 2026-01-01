<?php

namespace App\Models;

use App\Models\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'organization_id',
        'department_id',
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

    // Связи
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
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

    // Global Scope для автоматической фильтрации
    protected static function booted()
    {
        static::addGlobalScope(new OrganizationScope);

        // Автоматическое заполнение organization_id и department_id при создании
        static::creating(function ($client) {
            if (auth()->check()) {
                $user = auth()->user();
                if (!$client->organization_id && $user->organization_id) {
                    $client->organization_id = $user->organization_id;
                }
                if (!$client->department_id && $user->department_id) {
                    $client->department_id = $user->department_id;
                }
            }
        });
    }
}
