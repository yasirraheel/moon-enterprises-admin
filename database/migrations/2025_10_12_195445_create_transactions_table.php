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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->decimal('transaction_amount', 15, 2);
            $table->enum('type', ['debit', 'credit']);
            $table->decimal('remaining_balance', 15, 2);
            $table->enum('transaction_type', [
                'deposit',
                'withdrawal',
                'paid_service',
                'order_placed',
                'admin_credit',
                'admin_debit',
                'signup_bonus',
                'refund'
            ]);
            $table->string('description')->nullable();
            $table->string('reference_id')->nullable(); // ID of related record (deposit_id, order_id, etc.)
            $table->string('reference_type')->nullable(); // Model name (Deposits, Orders, PaidServiceSale, etc.)
            $table->json('metadata')->nullable(); // Additional data like order details, service info, etc.
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'created_at']);
            $table->index(['transaction_type', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
