<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->string('payment_id')->nullable()->unique();
            $table->string('status')->default('pending');
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('RUB');
            
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('months');
            
            $table->json('provider_data')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['organization_id', 'status']);
            $table->index('payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
