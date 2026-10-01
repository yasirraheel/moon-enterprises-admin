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
            if (!Schema::hasColumn('admin_settings', 'app_closed')) {
                $table->boolean('app_closed')->default(false);
            }
            if (!Schema::hasColumn('admin_settings', 'app_closed_title')) {
                $table->string('app_closed_title')->nullable();
            }
            if (!Schema::hasColumn('admin_settings', 'app_closed_message')) {
                $table->text('app_closed_message')->nullable();
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
            foreach (['app_closed', 'app_closed_title', 'app_closed_message'] as $col) {
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
