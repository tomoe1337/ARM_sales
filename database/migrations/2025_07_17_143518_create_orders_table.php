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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // BlueSales integration
            $table->string('bluesales_id')->unique();
            
            // Relations
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // manager
            $table->foreignId('deal_id')->nullable()->constrained()->onDelete('set null'); // связь со сделкой
            
            // Order status from BlueSales (not enum - using string as per project rules)
            $table->string('status')->default('new'); // 'new', 'reserve', 'preorder', 'shipped', 'delivered', 'cancelled'
            
            // Order details
            $table->string('internal_number')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('discount', 5, 4)->default(0); // percentage
            $table->decimal('prepay', 10, 2)->default(0);
            
            // Comments
            $table->text('customer_comments')->nullable();
            $table->text('internal_comments')->nullable();
            
            // Sync metadata
            $table->timestamp('bluesales_last_sync')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};