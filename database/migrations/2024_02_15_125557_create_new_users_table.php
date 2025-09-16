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
        Schema::create('new_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('full_name');
            $table->string('login')->unique();
            $table->string('email')->unique();
            $table->string('rights')->default('manager');
            $table->string('role')->default('manager');
            $table->string('avatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // Удаляем старую таблицу
        Schema::dropIfExists('users');

        // Переименовываем новую таблицу
        Schema::rename('new_users', 'users');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
