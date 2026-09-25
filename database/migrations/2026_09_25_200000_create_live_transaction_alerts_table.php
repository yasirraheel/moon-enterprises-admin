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
        // 1. Add social proof / live alerts columns to admin_settings table
        Schema::table('admin_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_settings', 'online_users_enabled')) {
                $table->boolean('online_users_enabled')->default(true);
            }
            if (!Schema::hasColumn('admin_settings', 'online_users_base')) {
                $table->integer('online_users_base')->default(452);
            }
            if (!Schema::hasColumn('admin_settings', 'online_users_min')) {
                $table->integer('online_users_min')->default(420);
            }
            if (!Schema::hasColumn('admin_settings', 'online_users_max')) {
                $table->integer('online_users_max')->default(490);
            }
            if (!Schema::hasColumn('admin_settings', 'online_users_interval')) {
                $table->integer('online_users_interval')->default(6);
            }
            if (!Schema::hasColumn('admin_settings', 'transaction_alerts_enabled')) {
                $table->boolean('transaction_alerts_enabled')->default(true);
            }
            if (!Schema::hasColumn('admin_settings', 'transaction_alerts_interval')) {
                $table->integer('transaction_alerts_interval')->default(10);
            }
        });

        // 2. Create live_transaction_alerts table
        if (!Schema::hasTable('live_transaction_alerts')) {
            Schema::create('live_transaction_alerts', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->enum('type', ['withdrawal', 'deposit'])->default('withdrawal');
                $table->decimal('amount', 12, 2)->default(5000);
                $table->string('time_ago', 50)->default('just now');
                $table->string('custom_message', 255)->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });

            // Seed initial realistic Pakistani transactions
            $initialAlerts = [
                ['name' => 'Ahmed Ali', 'type' => 'withdrawal', 'amount' => 5000, 'time_ago' => 'just now'],
                ['name' => 'Muhammad Hassan', 'type' => 'deposit', 'amount' => 10000, 'time_ago' => '1 min ago'],
                ['name' => 'Fatima Bibi', 'type' => 'withdrawal', 'amount' => 7500, 'time_ago' => '2 mins ago'],
                ['name' => 'Ali Raza', 'type' => 'deposit', 'amount' => 3500, 'time_ago' => '3 mins ago'],
                ['name' => 'Zainab Noor', 'type' => 'withdrawal', 'amount' => 12000, 'time_ago' => '4 mins ago'],
                ['name' => 'Umar Farooq', 'type' => 'deposit', 'amount' => 15000, 'time_ago' => '5 mins ago'],
                ['name' => 'Ayesha Siddique', 'type' => 'withdrawal', 'amount' => 20000, 'time_ago' => 'just now'],
                ['name' => 'Bilal Ahmed', 'type' => 'deposit', 'amount' => 2000, 'time_ago' => '2 mins ago'],
                ['name' => 'Maria Khan', 'type' => 'withdrawal', 'amount' => 25000, 'time_ago' => '1 min ago'],
                ['name' => 'Tariq Mahmood', 'type' => 'deposit', 'amount' => 5000, 'time_ago' => '4 mins ago'],
                ['name' => 'Sana Malik', 'type' => 'withdrawal', 'amount' => 8000, 'time_ago' => 'just now'],
                ['name' => 'Hamza Sheikh', 'type' => 'deposit', 'amount' => 12000, 'time_ago' => '3 mins ago'],
            ];

            $now = now();
            foreach ($initialAlerts as $index => $alert) {
                DB::table('live_transaction_alerts')->insert([
                    'name' => $alert['name'],
                    'type' => $alert['type'],
                    'amount' => $alert['amount'],
                    'time_ago' => $alert['time_ago'],
                    'status' => 'active',
                    'sort_order' => $index + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_transaction_alerts');

        Schema::table('admin_settings', function (Blueprint $table) {
            $table->dropColumn([
                'online_users_enabled',
                'online_users_base',
                'online_users_min',
                'online_users_max',
                'online_users_interval',
                'transaction_alerts_enabled',
                'transaction_alerts_interval',
            ]);
        });
    }
};
