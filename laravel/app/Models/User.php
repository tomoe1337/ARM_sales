<?php

namespace App\Models;

use App\Enums\UserRolesEnum;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Builder;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
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

    /**
     * Получить URL аватарки пользователя
     */
    public function getAvatarUrl(): string
    {
        if (!$this->avatar) {
            return asset('storage/avatars/default_avatar.png');
        }

        // Если это полный URL, возвращаем как есть
        if (str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }

        // Используем Storage для получения URL
        // Storage::url() для диска 'public' вернет путь вида /storage/path/to/file
        // Не проверяем существование файла здесь, чтобы избежать лишних проверок
        // Если файл не существует, браузер покажет дефолтный через onerror
        return Storage::disk('public')->url($this->avatar);
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

    /**
     * Локальный scope: пользователи отдела текущего пользователя
     */
    public function scopeForDepartment(Builder $query, ?int $departmentId = null): Builder
    {
        $user = auth()->user();
        $departmentId = $departmentId ?? $user->department_id;
        
        return $query->where('department_id', $departmentId);
    }

    /**
     * Локальный scope: пользователи организации текущего пользователя
     */
    public function scopeForOrganization(Builder $query, ?int $organizationId = null): Builder
    {
        $user = auth()->user();
        $organizationId = $organizationId ?? $user->organization_id;
        
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Локальный scope: только менеджеры
     */
    public function scopeManagers(Builder $query): Builder
    {
        return $query->where('role', UserRolesEnum::MANAGER->value);
    }

    /**
     * Локальный scope: автоматическая фильтрация по контексту пользователя
     * Применяет фильтрацию в зависимости от роли
     */
    public function scopeForCurrentUserContext(Builder $query): Builder
    {
        if (!auth()->check()) {
            return $query;
        }

        $user = auth()->user();
        
        if (!$user->organization_id) {
            return $query;
        }

        // Супер-админ организации видит всех пользователей своей организации
        if ($user->isOrganizationAdmin()) {
            return $query->where('organization_id', $user->organization_id);
        }
        
        // Руководитель отдела видит всех пользователей своего отдела
        if ($user->isHead()) {
            return $query->where('organization_id', $user->organization_id)
                        ->where('department_id', $user->department_id);
        }
        
        // Менеджер видит только себя
        return $query->where('id', $user->id);
    }
}
