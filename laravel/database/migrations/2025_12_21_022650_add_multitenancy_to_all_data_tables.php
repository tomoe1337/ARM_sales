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
        // Добавляем organization_id и department_id во все таблицы с данными
        $tables = [
            'clients',
            'deals',
            'orders',
            'tasks',
            'plans',
            'work_sessions',
            'analysis_ai_reports',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('organization_id')->after('id')->constrained('organizations')->onDelete('cascade');
                $table->foreignId('department_id')->after('organization_id')->constrained('departments')->onDelete('restrict');
                $table->index(['organization_id', 'department_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'clients',
            'deals',
            'orders',
            'tasks',
            'plans',
            'work_sessions',
            'analysis_ai_reports',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropIndex(['organization_id', 'department_id']);
                $table->dropForeign(['organization_id']);
                $table->dropForeign(['department_id']);
                $table->dropColumn(['organization_id', 'department_id']);
            });
        }
    }
};
