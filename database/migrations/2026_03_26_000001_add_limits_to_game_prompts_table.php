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
            $table->decimal('first_limit', 12, 2)->nullable()->after('number_end');
            $table->decimal('second_limit', 12, 2)->nullable()->after('first_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_prompts', function (Blueprint $table) {
            $table->dropColumn(['first_limit', 'second_limit']);
        });
    }
};
