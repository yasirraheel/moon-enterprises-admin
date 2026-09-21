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
        Schema::table('apk_versions', function (Blueprint $table) {
            // Add the download_link column
            $table->string('download_link')->after('version_code');
            
            // Drop the old file-related columns
            $table->dropColumn(['file_name', 'file_path', 'file_size']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apk_versions', function (Blueprint $table) {
            // Add back the old columns
            $table->string('file_name')->after('version_code');
            $table->string('file_path')->after('file_name');
            $table->string('file_size')->after('file_path');
            
            // Drop the download_link column
            $table->dropColumn('download_link');
        });
    }
};
