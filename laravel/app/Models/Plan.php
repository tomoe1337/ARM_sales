<?php

namespace App\Models;

use App\Models\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
        'department_id',
        'monthly_plan',
        'daily_plan'
    ];

    protected $casts = [
        'monthly_plan' => 'decimal:2',
        'daily_plan' => 'decimal:2'
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
        static::creating(function ($plan) {
            if (auth()->check()) {
                $user = auth()->user();
                if (!$plan->organization_id && $user->organization_id) {
                    $plan->organization_id = $user->organization_id;
                }
                if (!$plan->department_id && $user->department_id) {
                    $plan->department_id = $user->department_id;
                }
            }
        });
    }
} 