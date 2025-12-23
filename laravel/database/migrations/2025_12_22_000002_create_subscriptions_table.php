<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->unique()->constrained('departments')->onDelete('cascade');
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->onDelete('restrict');
            
            // Статус подписки
            $table->string('status')->default('trial');
            
            // Период подписки
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('trial_ends_at')->nullable(); // Конец пробного периода (14 дней)
            $table->timestamp('canceled_at')->nullable();
            
            // Лимит и цена
            $table->integer('paid_users_limit')->default(0); // Текущий лимит платных пользователей
            $table->decimal('monthly_price', 10, 2)->default(0); // Месячная стоимость (лимит × цена за пользователя)
            
            // Автопродление
            $table->boolean('auto_renew')->default(false);
            
            $table->timestamps();
            
            $table->index('ends_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

