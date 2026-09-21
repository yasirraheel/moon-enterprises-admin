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
        Schema::table('deposits', function (Blueprint $table) {
            // Check if status column exists and modify it
            if (Schema::hasColumn('deposits', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected'])
                      ->default('pending')
                      ->change();
            } else {
                // If status column doesn't exist, create it
                $table->enum('status', ['pending', 'approved', 'rejected'])
                      ->default('pending')
                      ->after('payment_proof');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            if (Schema::hasColumn('deposits', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
