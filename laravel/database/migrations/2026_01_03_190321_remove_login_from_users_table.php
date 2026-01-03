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
        // Удаляем constraint напрямую через SQL, если он существует
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_login_unique");
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS new_users_login_unique");
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('login');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('login')->unique()->after('full_name');
        });
    }
};
