<?php

namespace App\Models;

use App\Enums\UserRolesEnum;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'email',
        'password',
        'role',
        'status',
        'monthly_plan',
        'daily_plan',
        'is_active',
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
    ];

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
        return $this->role === UserRolesEnum::HEAD->value;
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
        return $this->role === UserRolesEnum::ADMIN->value;
    }

}
