<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false)->after('department_id'); // Входит ли в лимит платных пользователей
            $table->timestamp('paid_activated_at')->nullable()->after('is_paid'); // Когда активирован в лимите
            $table->timestamp('free_until')->nullable()->after('paid_activated_at'); // Резерв: для будущей реализации пропорционального расчета
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_paid', 'paid_activated_at', 'free_until']);
        });
    }
};

