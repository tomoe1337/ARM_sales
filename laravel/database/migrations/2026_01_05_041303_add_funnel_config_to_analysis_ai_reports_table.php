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
        Schema::table('analysis_ai_reports', function (Blueprint $table) {
            $table->json('funnel_config')->nullable()->after('employee_stats');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analysis_ai_reports', function (Blueprint $table) {
            $table->dropColumn('funnel_config');
        });
    }
};
