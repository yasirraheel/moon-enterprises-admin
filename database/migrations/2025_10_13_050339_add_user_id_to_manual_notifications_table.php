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
        Schema::table('manual_notifications', function (Blueprint $table) {
            // Only add columns if they don't exist
            if (!Schema::hasColumn('manual_notifications', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('manual_notifications', 'type')) {
                $table->string('type')->default('public')->after('user_id'); // 'public' or 'user_specific'
            }
            if (!Schema::hasColumn('manual_notifications', 'action_type')) {
                $table->string('action_type')->nullable()->after('type'); // 'balance_added', 'deposit_approved', etc.
            }
            if (!Schema::hasColumn('manual_notifications', 'metadata')) {
                $table->json('metadata')->nullable()->after('action_type'); // Additional data like amount, reference_id, etc.
            }
        });
        
        // Add index separately to avoid issues
        Schema::table('manual_notifications', function (Blueprint $table) {
            if (!Schema::hasIndex('manual_notifications', 'manual_notifications_user_id_type_is_active_index')) {
                $table->index(['user_id', 'type', 'is_active']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manual_notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'type', 'is_active']);
            $table->dropColumn(['user_id', 'type', 'action_type', 'metadata']);
        });
    }
};
