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
        Schema::table('department_bluesales_credentials', function (Blueprint $table) {
            $table->text('api_key')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('department_bluesales_credentials', function (Blueprint $table) {
            // При откате устанавливаем пустую строку для NULL значений, затем делаем NOT NULL
            \DB::table('department_bluesales_credentials')
                ->whereNull('api_key')
                ->update(['api_key' => '']);
            
            $table->text('api_key')->nullable(false)->change();
        });
    }
};
