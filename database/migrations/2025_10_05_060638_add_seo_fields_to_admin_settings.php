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
            // SEO Meta Tags
            $table->string('seo_title', 200)->nullable()->after('title');
            $table->text('seo_description')->nullable()->after('seo_title');
            $table->text('seo_keywords')->nullable()->after('seo_description');
            
            // Open Graph Tags
            $table->string('og_title', 200)->nullable()->after('seo_keywords');
            $table->text('og_description')->nullable()->after('og_title');
            $table->string('og_image', 500)->nullable()->after('og_description');
            $table->string('og_type', 50)->default('website')->after('og_image');
            $table->string('og_site_name', 200)->nullable()->after('og_type');
            
            // Twitter Card Tags
            $table->string('twitter_card', 50)->default('summary_large_image')->after('og_site_name');
            $table->string('twitter_site', 100)->nullable()->after('twitter_card');
            $table->string('twitter_creator', 100)->nullable()->after('twitter_site');
            
            // Additional SEO
            $table->string('canonical_url', 500)->nullable()->after('twitter_creator');
            $table->string('robots', 100)->default('index,follow')->after('canonical_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_settings', function (Blueprint $table) {
            $table->dropColumn([
                'seo_title',
                'seo_description', 
                'seo_keywords',
                'og_title',
                'og_description',
                'og_image',
                'og_type',
                'og_site_name',
                'twitter_card',
                'twitter_site',
                'twitter_creator',
                'canonical_url',
                'robots'
            ]);
        });
    }
};