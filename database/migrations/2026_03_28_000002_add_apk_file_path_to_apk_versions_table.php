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
            $table->string('apk_file_path')->nullable()->after('download_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apk_versions', function (Blueprint $table) {
            $table->dropColumn('apk_file_path');
        });
    }
};
