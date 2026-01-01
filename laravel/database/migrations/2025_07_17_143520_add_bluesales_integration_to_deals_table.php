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
        Schema::table('deals', function (Blueprint $table) {
            // BlueSales integration fields
            $table->string('bluesales_source')->nullable()->after('closed_at'); // откуда пришла сделка (bluesales, manual, etc.)
            $table->integer('related_orders_count')->default(0)->after('bluesales_source'); // количество связанных заказов
            $table->decimal('orders_total_amount', 10, 2)->default(0)->after('related_orders_count'); // общая сумма заказов
            
            // Sync metadata
            $table->timestamp('bluesales_last_sync')->nullable()->after('orders_total_amount');
            
            // Дополнительные поля для интеграции
            $table->text('integration_notes')->nullable()->after('bluesales_last_sync'); // заметки по интеграции
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn([
                'bluesales_source',
                'related_orders_count',
                'orders_total_amount',
                'bluesales_last_sync',
                'integration_notes'
            ]);
        });
    }
};