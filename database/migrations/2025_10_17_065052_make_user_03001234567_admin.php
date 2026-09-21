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
        // First, ensure the roles_and_permissions table exists and has the Super Admin role
        if (!Schema::hasTable('roles_and_permissions')) {
            Schema::create('roles_and_permissions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 250)->index('name');
                $table->longText('permissions')->index();
                $table->enum('editable', ['0', '1'])->default('1');
                $table->timestamps();
            });
        }

        // Insert Super Admin role if it doesn't exist
        $superAdminRole = DB::table('roles_and_permissions')
            ->where('name', 'Super Admin')
            ->where('permissions', 'full_access')
            ->first();

        if (!$superAdminRole) {
            $superAdminRoleId = DB::table('roles_and_permissions')->insertGetId([
                'name' => 'Super Admin',
                'permissions' => 'full_access',
                'editable' => '0',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            $superAdminRoleId = $superAdminRole->id;
        }

        // Ensure the role column exists in users table
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('role')->nullable()->after('balance');
            });
        }

        // Update the user with username '03001234567' to have admin role
        // First try with the exact username, then try without leading zero
        $userUpdated = DB::table('users')
            ->where('username', '03001234567')
            ->update(['role' => $superAdminRoleId]);

        if (!$userUpdated) {
            // Try without leading zero
            $userUpdated = DB::table('users')
                ->where('username', '3001234567')
                ->update(['role' => $superAdminRoleId]);
        }

        if (!$userUpdated) {
            // Create the user if it doesn't exist
            DB::table('users')->insert([
                'username' => '03001234567',
                'password' => bcrypt('password123'), // Default password
                'status' => 'active',
                'role' => $superAdminRoleId,
                'balance' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove admin role from the user
        DB::table('users')
            ->where('username', '03001234567')
            ->update(['role' => null]);
    }
};
