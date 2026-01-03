<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class DepartmentBluesalesCredential extends Model
{
    protected $table = 'department_bluesales_credentials';

    protected $fillable = [
        'department_id',
        'login',
        'api_key',
        'sync_enabled',
        'last_sync_at',
        'last_sync_error',
    ];

    protected $casts = [
        'sync_enabled' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    protected $hidden = [
        'api_key', // Не показываем в JSON по умолчанию
    ];

    /**
     * Связь с отделом
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Получить расшифрованный API ключ
     */
    public function getDecryptedApiKey(): ?string
    {
        if (empty($this->api_key)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->api_key);
        } catch (\Exception $e) {
            \Log::error('Failed to decrypt BlueSales API key', [
                'department_id' => $this->department_id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Установить зашифрованный API ключ
     */
    public function setApiKeyAttribute($value): void
    {
        if (!empty($value)) {
            $this->attributes['api_key'] = Crypt::encryptString($value);
        } else {
            // Явно устанавливаем null для пустых значений
            $this->attributes['api_key'] = null;
        }
    }

    /**
     * Проверить, готовы ли креды для синхронизации
     */
    public function isReadyForSync(): bool
    {
        return $this->sync_enabled 
            && !empty($this->login) 
            && !empty($this->api_key);
    }

    /**
     * Обновить время последней синхронизации
     */
    public function markSyncSuccess(): void
    {
        $this->update([
            'last_sync_at' => now(),
            'last_sync_error' => null,
        ]);
    }

    /**
     * Сохранить ошибку синхронизации
     */
    public function markSyncError(string $error): void
    {
        $this->update([
            'last_sync_error' => $error,
        ]);
    }
}

