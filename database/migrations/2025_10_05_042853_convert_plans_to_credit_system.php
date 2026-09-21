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
        Schema::table('plans', function (Blueprint $table) {
            // Remove photo-related columns
            $table->dropColumn([
                'downloadable_content',
                'downloads_per_month', 
                'download_limits',
                'license',
                'popular'
            ]);
            
            // Add credit-related columns
            $table->unsignedInteger('credits')->default(0)->after('price_year');
            $table->enum('duration', ['month', 'year'])->default('month')->after('credits');
            $table->boolean('unused_credits_rollover')->default(false)->after('duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // Add back photo-related columns
            $table->string('downloadable_content', 100)->default('images');
            $table->unsignedInteger('downloads_per_month')->default(1);
            $table->unsignedInteger('download_limits')->default(0);
            $table->string('license', 100)->default('regular');
            $table->boolean('popular')->default(false);
            
            // Remove credit-related columns
            $table->dropColumn([
                'credits',
                'duration', 
                'unused_credits_rollover'
            ]);
        });
    }
};