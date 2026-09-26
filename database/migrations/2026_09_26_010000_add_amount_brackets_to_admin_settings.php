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
        Schema::table('admin_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_settings', 'withdrawal_min_amount')) {
                $table->decimal('withdrawal_min_amount', 12, 2)->default(2000);
            }
            if (!Schema::hasColumn('admin_settings', 'withdrawal_max_amount')) {
                $table->decimal('withdrawal_max_amount', 12, 2)->default(25000);
            }
            if (!Schema::hasColumn('admin_settings', 'deposit_min_amount')) {
                $table->decimal('deposit_min_amount', 12, 2)->default(1000);
            }
            if (!Schema::hasColumn('admin_settings', 'deposit_max_amount')) {
                $table->decimal('deposit_max_amount', 12, 2)->default(20000);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_settings', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['withdrawal_min_amount', 'withdrawal_max_amount', 'deposit_min_amount', 'deposit_max_amount'] as $col) {
                if (Schema::hasColumn('admin_settings', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
