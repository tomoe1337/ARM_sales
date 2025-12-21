<?php

namespace App\Models;

use App\Models\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkSession extends Model
{
    protected $fillable = [
        'user_id',
        'organization_id',
        'department_id',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
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

    // Global Scope для автоматической фильтрации
    protected static function booted()
    {
        static::addGlobalScope(new OrganizationScope);

        // Автоматическое заполнение organization_id и department_id при создании
        static::creating(function ($workSession) {
            if (auth()->check()) {
                $user = auth()->user();
                if (!$workSession->organization_id && $user->organization_id) {
                    $workSession->organization_id = $user->organization_id;
                }
                if (!$workSession->department_id && $user->department_id) {
                    $workSession->department_id = $user->department_id;
                }
            }
        });
    }
} 