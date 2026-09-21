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
            // Update the enum to include 'OK' and 'WIN' as valid statuses
            $table->enum('status', ['pending', 'approved', 'rejected', 'OK', 'WIN'])->default('pending')->change();

            // Add n_p column with N/P options
            $table->enum('n_p', ['N', 'P'])->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Revert back to original enum values
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->change();

            // Drop the n_p column
            $table->dropColumn('n_p');
        });
    }
};
