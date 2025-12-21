<?php

namespace App\Models;

use App\Enums\UserRolesEnum;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'full_name',
        'login',
        'email',
        'password',
        'role',
        'status',
        'monthly_plan',
        'daily_plan',
        'organization_id',
        'department_id',
        'is_active',
        'activated_at',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
    ];

    // Связи с мультитенантностью
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function workSessions(): HasMany
    {
        return $this->hasMany(WorkSession::class);
    }

    public function isManager(): bool
    {
        return $this->role === UserRolesEnum::MANAGER->value;
    }

    public function isHead(): bool
    {
        return $this->role === UserRolesEnum::HEAD->value 
            && $this->department_id !== null;
    }

    public function isOrganizationAdmin(): bool
    {
        return $this->role === UserRolesEnum::ADMIN->value 
            && $this->department_id === null; // Супер-админ без привязки к отделу
    }

    // Проверка принадлежности к отделу
    public function belongsToDepartment(int $departmentId): bool
    {
        return $this->department_id === $departmentId;
    }

    // Проверка принадлежности к организации
    public function belongsToOrganization(int $organizationId): bool
    {
        return $this->organization_id === $organizationId;
    }

    public function isWorking(): bool
    {
        return $this->workSessions()
            ->whereNull('end_time')
            ->exists();
    }

    public function getCurrentSession(): ?WorkSession
    {
        return $this->workSessions()
            ->whereNull('end_time')
            ->latest()
            ->first();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Доступ имеют: админы, руководители отделов и менеджеры
        return in_array($this->role, [
            UserRolesEnum::ADMIN->value,
            UserRolesEnum::HEAD->value,
            UserRolesEnum::MANAGER->value,
        ]);
    }

}
