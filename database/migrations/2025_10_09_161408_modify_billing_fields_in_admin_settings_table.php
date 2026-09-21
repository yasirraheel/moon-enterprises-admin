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
            // Rename company to bank_or_account_name
            $table->renameColumn('company', 'bank_or_account_name');

            // Rename address to account_title
            $table->renameColumn('address', 'account_title');

            // Rename city to account_no
            $table->renameColumn('city', 'account_no');

            // Drop unused fields
            $table->dropColumn(['country', 'zip', 'vat']);

            // Add new field for bank/account image
            $table->string('bank_image', 500)->nullable()->after('account_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_settings', function (Blueprint $table) {
            // Revert column renames
            $table->renameColumn('bank_or_account_name', 'company');
            $table->renameColumn('account_title', 'address');
            $table->renameColumn('account_no', 'city');

            // Drop bank_image field
            $table->dropColumn('bank_image');

            // Re-add dropped fields
            $table->string('country', 100)->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('vat', 50)->nullable();
        });
    }
};
