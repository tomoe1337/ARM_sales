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
        Schema::table('users', function (Blueprint $table) {
            // Добавляем связи с организацией и отделом
            $table->foreignId('organization_id')->nullable()->after('role')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->after('organization_id')->constrained('departments')->onDelete('set null');
            
            // Статус активации (если еще нет)
            if (!Schema::hasColumn('users', 'activated_at')) {
                $table->timestamp('activated_at')->nullable()->after('is_active');
            }
            
            // Индекс для быстрого поиска
            $table->index(['organization_id', 'department_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Удаляем индекс
            $table->dropIndex(['organization_id', 'department_id']);
            
            // Удаляем поля
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn(['organization_id', 'department_id']);
            
            // Удаляем activated_at если был добавлен
            if (Schema::hasColumn('users', 'activated_at')) {
                $table->dropColumn('activated_at');
            }
        });
    }
};
