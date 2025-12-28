<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Organization extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'subscription_plan_id',
        'subscription_expires_at',
        'trial_used_at',
        'is_active',
        'is_single_department',
        'settings',
    ];

    protected $casts = [
        'subscription_expires_at' => 'datetime',
        'trial_used_at' => 'datetime',
        'is_active' => 'boolean',
        'is_single_department' => 'boolean',
        'settings' => 'array',
    ];

    // Связи
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Проверка и обновление флага is_single_department
     */
    public function checkIfSingleDepartment(): bool
    {
        $departmentsCount = $this->departments()->where('is_active', true)->count();
        
        if ($departmentsCount <= 1) {
            $this->update(['is_single_department' => true]);
            return true;
        }
        
        $this->update(['is_single_department' => false]);
        return false;
    }

    /**
     * Проверка, использовала ли организация пробный период
     */
    public function hasUsedTrial(): bool
    {
        if ($this->trial_used_at !== null) {
            return true;
        }

        // Если организация уже что-то оплачивала, пробный период ей больше не положен
        return $this->payments()->where('status', 'succeeded')->exists();
    }
}
