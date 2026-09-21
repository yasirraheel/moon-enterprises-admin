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
            $table->decimal('min_bond_amount', 10, 2)->default(0)->after('decimal_format');
            $table->decimal('max_bond_amount', 10, 2)->default(0)->after('min_bond_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_settings', function (Blueprint $table) {
            $table->dropColumn(['min_bond_amount', 'max_bond_amount']);
        });
    }
};