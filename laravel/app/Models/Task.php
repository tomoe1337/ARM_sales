<?php

namespace App\Models;

use App\Models\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'deadline',
        'status',
        'user_id',
        'assignee_id',
        'organization_id',
        'department_id',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    // Связи
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
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
        static::creating(function ($task) {
            if (auth()->check()) {
                $user = auth()->user();
                if (!$task->organization_id && $user->organization_id) {
                    $task->organization_id = $user->organization_id;
                }
                if (!$task->department_id && $user->department_id) {
                    $task->department_id = $user->department_id;
                }
            }
        });
    }
}
