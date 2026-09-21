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
        // Ensure lottie_animation_url exists first
        if (!Schema::hasColumn('admin_settings', 'lottie_animation_url')) {
            Schema::table('admin_settings', function (Blueprint $table) {
                $table->string('lottie_animation_url')->nullable()->after('results_about_us');
            });
        }

        // Add countdown_video_url
        if (!Schema::hasColumn('admin_settings', 'countdown_video_url')) {
            Schema::table('admin_settings', function (Blueprint $table) {
                // Place after results_about_us to avoid dependency issues if lottie column isn't detected yet
                $table->string('countdown_video_url')->nullable()->after('results_about_us');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_settings', function (Blueprint $table) {
            if (Schema::hasColumn('admin_settings', 'countdown_video_url')) {
                $table->dropColumn('countdown_video_url');
            }
        });
    }
};
