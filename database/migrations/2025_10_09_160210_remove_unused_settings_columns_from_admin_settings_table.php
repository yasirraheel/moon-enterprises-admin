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
            $table->dropColumn([
                'sevenlabs_api_key',
                'sell_option',
                'who_can_sell',
                'who_can_upload',
                'show_images_index',
                'show_watermark',
                'free_photo_upload',
                'show_counter',
                'show_categories_index',
                'google_ads_index',
                'referral_system',
                'comments',
                'lightbox'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_settings', function (Blueprint $table) {
            $table->string('sevenlabs_api_key', 255)->nullable();
            $table->enum('sell_option', ['on', 'off'])->default('off');
            $table->enum('who_can_sell', ['all', 'admin'])->default('all');
            $table->enum('who_can_upload', ['all', 'admin'])->default('all');
            $table->enum('show_images_index', ['latest', 'featured', 'both'])->default('latest');
            $table->tinyInteger('show_watermark')->default(0);
            $table->enum('free_photo_upload', ['on', 'off'])->default('off');
            $table->enum('show_counter', ['on', 'off'])->default('off');
            $table->enum('show_categories_index', ['on', 'off'])->default('off');
            $table->enum('google_ads_index', ['on', 'off'])->default('off');
            $table->enum('referral_system', ['on', 'off'])->default('off');
            $table->tinyInteger('comments')->default(0);
            $table->enum('lightbox', ['on', 'off'])->default('off');
        });
    }
};
