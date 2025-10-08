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
        Schema::table('orders', function (Blueprint $table) {
            // Дополнительные поля для BlueSales интеграции
            $table->string('external_number')->nullable()->after('internal_number');
            $table->date('order_date')->nullable()->after('external_number');
            $table->decimal('money_discount', 10, 2)->default(0)->after('discount');
            $table->decimal('delivery_cost', 10, 2)->default(0)->after('money_discount');
            $table->string('tracking_number')->nullable()->after('delivery_cost');
            $table->integer('delivery_service')->nullable()->after('tracking_number');
            $table->text('delivery_info')->nullable()->after('delivery_service'); // JSON данные о доставке
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'external_number',
                'order_date',
                'money_discount',
                'delivery_cost',
                'tracking_number',
                'delivery_service',
                'delivery_info'
            ]);
        });
    }
};
