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
        Schema::table('clients', function (Blueprint $table) {
            // BlueSales integration fields
            $table->string('bluesales_id')->nullable()->unique()->after('id');
            $table->string('full_name')->nullable()->after('name');
            $table->string('country')->nullable()->after('address');
            $table->string('city')->nullable()->after('country');
            $table->date('birth_date')->nullable()->after('city');
            $table->string('gender')->nullable()->after('birth_date'); // 'male', 'female'
            
            // Social networks
            $table->string('vk_id')->nullable()->after('gender');
            $table->string('ok_id')->nullable()->after('vk_id');
            
            // CRM fields
            $table->string('crm_status')->nullable()->after('ok_id');
            $table->datetime('first_contact_date')->nullable()->after('crm_status');
            $table->datetime('next_contact_date')->nullable()->after('first_contact_date');
            $table->string('source')->nullable()->after('next_contact_date');
            $table->string('sales_channel')->nullable()->after('source');
            $table->text('tags')->nullable()->after('sales_channel');
            $table->text('notes')->nullable()->after('tags');
            
            // Additional contacts
            $table->text('additional_contacts')->nullable()->after('notes');
            
            // Sync metadata
            $table->timestamp('bluesales_last_sync')->nullable()->after('additional_contacts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'bluesales_id',
                'full_name',
                'country',
                'city',
                'birth_date',
                'gender',
                'vk_id',
                'ok_id',
                'crm_status',
                'first_contact_date',
                'next_contact_date',
                'source',
                'sales_channel',
                'tags',
                'notes',
                'additional_contacts',
                'bluesales_last_sync'
            ]);
        });
    }
};