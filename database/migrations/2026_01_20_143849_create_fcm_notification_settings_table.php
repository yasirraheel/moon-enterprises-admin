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
        Schema::create('fcm_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('notification_type')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        // Seed default settings
        $settings = [
            [
                'notification_type' => 'deposit_approved',
                'label' => 'Deposit Approved',
                'description' => 'Sent when a user\'s deposit request is approved.',
                'is_enabled' => true,
            ],
            [
                'notification_type' => 'deposit_rejected',
                'label' => 'Deposit Rejected',
                'description' => 'Sent when a user\'s deposit request is rejected.',
                'is_enabled' => true,
            ],
            [
                'notification_type' => 'dealership_approved',
                'label' => 'Dealership Approved',
                'description' => 'Sent when a dealership request is approved.',
                'is_enabled' => true,
            ],
            [
                'notification_type' => 'dealership_rejected',
                'label' => 'Dealership Rejected',
                'description' => 'Sent when a dealership request is rejected.',
                'is_enabled' => true,
            ],
            [
                'notification_type' => 'order_approved',
                'label' => 'Order Approved',
                'description' => 'Sent when an order is successfully placed/approved.',
                'is_enabled' => true,
            ],
            [
                'notification_type' => 'order_rejected',
                'label' => 'Order Rejected',
                'description' => 'Sent when an order is rejected.',
                'is_enabled' => true,
            ],
            [
                'notification_type' => 'order_first_win',
                'label' => 'Order First Prize Winner',
                'description' => 'Sent when a user wins the first prize.',
                'is_enabled' => true,
            ],
            [
                'notification_type' => 'order_second_win',
                'label' => 'Order Second Prize Winner',
                'description' => 'Sent when a user wins the second prize.',
                'is_enabled' => true,
            ],
            [
                'notification_type' => 'manual_notification',
                'label' => 'Manual Notifications',
                'description' => 'Allow sending manual notifications from admin panel.',
                'is_enabled' => true,
            ],
        ];

        DB::table('fcm_notification_settings')->insert($settings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcm_notification_settings');
    }
};
