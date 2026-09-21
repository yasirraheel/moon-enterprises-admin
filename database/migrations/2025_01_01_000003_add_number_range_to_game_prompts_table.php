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
        Schema::table('game_prompts', function (Blueprint $table) {
            $table->string('number_start')->nullable()->after('prompt');
            $table->string('number_end')->nullable()->after('number_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_prompts', function (Blueprint $table) {
            $table->dropColumn(['number_start', 'number_end']);
        });
    }
};
