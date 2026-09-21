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
        Schema::create('orders_soft_deleted', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_id'); // Original order ID
            $table->unsignedBigInteger('user_id');
            $table->string('username');
            $table->string('user_phone');
            $table->string('game_name');
            $table->string('bond_name');
            $table->string('rttp');
            $table->string('first');
            $table->string('second');
            $table->enum('status', ['pending', 'approved', 'rejected', 'OK', 'WIN'])->default('pending');
            $table->enum('n_p', ['N', 'P'])->nullable()->after('status');
            $table->timestamp('deleted_at');
            $table->timestamp('original_created_at');
            $table->timestamp('original_updated_at');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('deleted_at');
            $table->index('original_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders_soft_deleted');
    }
};
