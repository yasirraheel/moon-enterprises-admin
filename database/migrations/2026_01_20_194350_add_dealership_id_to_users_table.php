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
        Schema::table('users', function (Blueprint $table) {
            $table->string('dealership_id', 10)->nullable()->unique()->after('id');
        });

        // Populate existing users
        $users = DB::table('users')->select('id')->get();
        foreach ($users as $user) {
            $dealershipId = $this->generateUniqueDealershipId();
            DB::table('users')->where('id', $user->id)->update(['dealership_id' => $dealershipId]);
        }
    }

    /**
     * Generate a unique 4-digit dealership ID.
     */
    private function generateUniqueDealershipId()
    {
        do {
            $id = (string) mt_rand(1000, 9999);
            // Check against DB to ensure uniqueness
            $exists = DB::table('users')->where('dealership_id', $id)->exists();
        } while ($exists);
        return $id;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('dealership_id');
        });
    }
};
