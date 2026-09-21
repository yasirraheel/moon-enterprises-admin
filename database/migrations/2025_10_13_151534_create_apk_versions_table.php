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
        Schema::create('apk_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version_name')->unique(); // e.g., "1.0.0", "2.1.3"
            $table->integer('version_code')->unique(); // e.g., 1, 2, 3 (for Android)
            $table->string('download_link'); // Direct download link to APK
            $table->text('release_notes')->nullable(); // What's new in this version
            $table->boolean('is_active')->default(true); // Whether this version is currently active
            $table->boolean('is_force_update')->default(false); // Whether users must update
            $table->integer('download_count')->default(0); // Track downloads
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apk_versions');
    }
};
