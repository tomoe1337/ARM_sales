<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_bluesales_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')
                ->constrained('departments')
                ->onDelete('cascade');
            $table->string('login')->comment('Email для входа в BlueSales');
            $table->text('api_key')->comment('API ключ (зашифрован)');
            $table->boolean('sync_enabled')->default(true)->comment('Включена ли автоматическая синхронизация');
            $table->timestamp('last_sync_at')->nullable()->comment('Время последней успешной синхронизации');
            $table->text('last_sync_error')->nullable()->comment('Последняя ошибка синхронизации');
            $table->timestamps();
            
            // Один отдел - одни креды
            $table->unique('department_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_bluesales_credentials');
    }
};

