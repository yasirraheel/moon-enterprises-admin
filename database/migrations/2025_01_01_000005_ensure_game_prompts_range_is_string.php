<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to modify the column type to string (VARCHAR)
        // This avoids the need for doctrine/dbal dependency
        DB::statement('ALTER TABLE game_prompts MODIFY number_start VARCHAR(255) NULL');
        DB::statement('ALTER TABLE game_prompts MODIFY number_end VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to integer if needed (optional, but good practice)
        // Note: converting string to int might lose data if it contains non-numeric characters
        DB::statement('ALTER TABLE game_prompts MODIFY number_start INT NULL');
        DB::statement('ALTER TABLE game_prompts MODIFY number_end INT NULL');
    }
};
