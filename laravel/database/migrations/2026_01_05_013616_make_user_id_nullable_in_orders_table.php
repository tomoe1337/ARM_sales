<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Удаляем foreign key
            $table->dropForeign(['user_id']);
        });
        
        // Изменяем колонку на nullable через raw SQL
        DB::statement('ALTER TABLE orders ALTER COLUMN user_id DROP NOT NULL');
        
        Schema::table('orders', function (Blueprint $table) {
            // Добавляем foreign key обратно
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Удаляем foreign key
            $table->dropForeign(['user_id']);
        });
        
        // Устанавливаем дефолтное значение для NULL значений перед изменением на NOT NULL
        $firstUser = DB::table('users')->first();
        if ($firstUser) {
            DB::table('orders')
                ->whereNull('user_id')
                ->update(['user_id' => $firstUser->id]);
        }
        
        // Изменяем колонку обратно на NOT NULL через raw SQL
        DB::statement('ALTER TABLE orders ALTER COLUMN user_id SET NOT NULL');
        
        Schema::table('orders', function (Blueprint $table) {
            // Добавляем foreign key обратно
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
