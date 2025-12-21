<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название компании
            $table->string('email')->nullable(); // Email организации
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            
            // Подписка и биллинг
            // TODO: После создания таблицы subscription_plans добавить constrained
            $table->unsignedBigInteger('subscription_plan_id')->nullable();
            $table->timestamp('subscription_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Флаг для упрощения интерфейса (организация с одним отделом)
            $table->boolean('is_single_department')->default(true);
            
            // Метаданные
            $table->json('settings')->nullable(); // Дополнительные настройки
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
