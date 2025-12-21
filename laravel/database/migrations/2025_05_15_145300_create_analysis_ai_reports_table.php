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
        Schema::create('analysis_ai_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('report_type')->default('weekly'); // weekly, monthly, custom и т.д.
            $table->date('start_date');
            $table->date('end_date');

            // Основные метрики
            $table->integer('total_leads')->default(0);
            $table->integer('in_progress_count')->default(0);
            $table->integer('won_count')->default(0);
            $table->integer('lost_count')->default(0);
            $table->decimal('revenue',15,2)->default(0);

            // Детали
            $table->json('employee_stats'); // JSON массив
            $table->text('done_well');
            $table->text('done_bad');
            $table->text('general_result');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_ai_reports');
    }
};
