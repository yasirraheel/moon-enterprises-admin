<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the status enum to include new values and remove old ones
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('OK', 'first_win', 'second_win', 'rejected') DEFAULT 'OK'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old enum values
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
    }
};
