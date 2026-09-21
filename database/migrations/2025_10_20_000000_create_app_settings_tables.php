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
        // Update admin_settings with Results App specific fields
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

        // Create news_tickers table
        if (!Schema::hasTable('news_tickers')) {
            Schema::create('news_tickers', function (Blueprint $table) {
                $table->id();
                $table->text('message');
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // Create ip_tv_links table
        if (!Schema::hasTable('ip_tv_links')) {
            Schema::create('ip_tv_links', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('url');
                $table->string('icon')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_settings', function (Blueprint $table) {
            $table->dropColumn(['results_app_logo', 'results_app_name', 'results_about_us', 'results_contact_no']);
        });

        Schema::dropIfExists('news_tickers');
        Schema::dropIfExists('ip_tv_links');
    }
};
