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
            $table->dropColumn(['avatar', 'cover', 'img_category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_settings', function (Blueprint $table) {
            $table->string('avatar', 100)->default('default.jpg');
            $table->string('cover', 100)->default('cover.jpg');
            $table->string('img_category', 100)->default('default.jpg');
        });
    }
};
