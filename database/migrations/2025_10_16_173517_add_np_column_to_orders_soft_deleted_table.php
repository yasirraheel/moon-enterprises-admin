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
        Schema::table('orders_soft_deleted', function (Blueprint $table) {
            // Add n_p column if it doesn't exist
            if (!Schema::hasColumn('orders_soft_deleted', 'n_p')) {
                $table->enum('n_p', ['N', 'P'])->nullable()->after('status');
            }

            // Update status enum to include WIN if not already included
            $table->enum('status', ['pending', 'approved', 'rejected', 'OK', 'WIN'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders_soft_deleted', function (Blueprint $table) {
            // Drop the n_p column
            $table->dropColumn('n_p');

            // Revert status enum to original values
            $table->enum('status', ['pending', 'approved', 'rejected', 'OK'])->default('pending')->change();
        });
    }
};
