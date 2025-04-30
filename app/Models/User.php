<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
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
        return $this->role === 'manager';
    }

    public function isHead(): bool
    {
        return $this->role === 'head';
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

    public function getAuthIdentifierName(): string
    {
        return 'login';
    }

    public function getAuthIdentifier()
    {
        return $this->login;
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'login';
    }
}
