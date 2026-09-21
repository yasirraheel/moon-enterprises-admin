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
            if (!Schema::hasColumn('admin_settings', 'results_app_logo')) {
                $table->string('results_app_logo', 100)->nullable();
            }
            if (!Schema::hasColumn('admin_settings', 'results_app_name')) {
                $table->string('results_app_name', 100)->nullable();
            }
            if (!Schema::hasColumn('admin_settings', 'results_about_us')) {
                $table->text('results_about_us')->nullable();
            }
            if (!Schema::hasColumn('admin_settings', 'results_contact_no')) {
                $table->string('results_contact_no', 50)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_settings', function (Blueprint $table) {
            $table->dropColumn(['results_app_logo', 'results_app_name', 'results_about_us', 'results_contact_no']);
        });
    }
};
