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
        Schema::table('user_read_public_notifications', function (Blueprint $table) {
            // Add unique constraint to prevent duplicate entries
            $table->unique(['user_id', 'notification_id'], 'user_notification_unique');

            // Add index for better performance
            $table->index(['user_id', 'read_at'], 'user_read_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_read_public_notifications', function (Blueprint $table) {
            $table->dropUnique('user_notification_unique');
            $table->dropIndex('user_read_at_index');
        });
    }
};
