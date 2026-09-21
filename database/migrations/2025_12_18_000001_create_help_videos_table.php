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
        Schema::create('help_videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('video_type', ['youtube', 'local'])->default('youtube');
            $table->string('youtube_url')->nullable();
            $table->string('local_video_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('view_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_videos');
    }
};
