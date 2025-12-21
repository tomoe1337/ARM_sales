<?php

namespace App\Models;

use App\Models\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'amount',
        'status',
        'closed_at',
        'user_id',
        'client_id',
        'organization_id',
        'department_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'closed_at' => 'datetime',
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
        static::creating(function ($deal) {
            if (auth()->check()) {
                $user = auth()->user();
                if (!$deal->organization_id && $user->organization_id) {
                    $deal->organization_id = $user->organization_id;
                }
                if (!$deal->department_id && $user->department_id) {
                    $deal->department_id = $user->department_id;
                }
            }
        });
    }
}
