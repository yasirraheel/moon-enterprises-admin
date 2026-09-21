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
            // Remove all payment-related columns except currency ones
            $table->dropColumn([
                'default_price_photos',
                'extended_license_price',
                'min_sale_amount',
                'max_sale_amount',
                'min_deposits_amount',
                'max_deposits_amount',
                'fee_commission',
                'fee_commission_non_exclusive',
                'percentage_referred',
                'referral_transaction_limit',
                'amount_min_withdrawal',
                'payout_method_paypal',
                'payout_method_bank',
                'stripe_connect',
                'tax_on_wallet',
                'stripe_connect_countries'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_settings', function (Blueprint $table) {
            $table->decimal('default_price_photos', 8, 2)->nullable();
            $table->decimal('extended_license_price', 8, 2)->nullable();
            $table->decimal('min_sale_amount', 8, 2)->nullable();
            $table->decimal('max_sale_amount', 8, 2)->nullable();
            $table->decimal('min_deposits_amount', 8, 2)->nullable();
            $table->decimal('max_deposits_amount', 8, 2)->nullable();
            $table->integer('fee_commission')->nullable();
            $table->integer('fee_commission_non_exclusive')->nullable();
            $table->integer('percentage_referred')->nullable();
            $table->integer('referral_transaction_limit')->nullable();
            $table->decimal('amount_min_withdrawal', 8, 2)->nullable();
            $table->boolean('payout_method_paypal')->default(false);
            $table->boolean('payout_method_bank')->default(false);
            $table->boolean('stripe_connect')->default(false);
            $table->boolean('tax_on_wallet')->default(false);
            $table->text('stripe_connect_countries')->nullable();
        });
    }
};