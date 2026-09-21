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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 10, 2);
            $table->string('account_number', 255);
            $table->string('account_title', 255);
            $table->string('bank_name', 255);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->string('payment_proof')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('date')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
