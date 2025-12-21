<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'head_id',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    // Связи
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Автоматическое обновление флага is_single_department в организации
     */
    protected static function booted()
    {
        static::saved(function ($department) {
            if ($department->organization) {
                $department->organization->checkIfSingleDepartment();
            }
        });

        static::deleted(function ($department) {
            if ($department->organization) {
                $department->organization->checkIfSingleDepartment();
            }
        });
    }
}
