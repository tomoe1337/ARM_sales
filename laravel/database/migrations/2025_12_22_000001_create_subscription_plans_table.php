<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Стандарт');
            $table->string('slug')->default('standard')->unique();
            $table->text('description')->nullable();
            
            // Ценообразование
            $table->decimal('price_per_user', 10, 2)->default(500); // 500₽ за пользователя в месяц
            
            // Функции (все включены по умолчанию)
            $table->boolean('ai_analytics_enabled')->default(true);
            $table->boolean('crm_sync_enabled')->default(true);
            
            // Метаданные
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};

