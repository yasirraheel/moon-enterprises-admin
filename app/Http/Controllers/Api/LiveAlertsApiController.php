<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminSettings;
use App\Models\LiveTransactionAlert;
use Illuminate\Support\Facades\Schema;

class LiveAlertsApiController extends Controller
{
    /**
     * Get Live Social Proof Data (Online Users Count & Transaction Alerts)
     */
    public function getLiveAlerts()
    {
        try {
            $settings = AdminSettings::first();

            $onlineUsersEnabled = true;
            $onlineUsersBase = 452;
            $onlineUsersMin = 420;
            $onlineUsersMax = 490;
            $onlineUsersInterval = 6;

            $txAlertsEnabled = true;
            $txAlertsInterval = 10;

            if ($settings) {
                if (isset($settings->online_users_enabled)) {
                    $onlineUsersEnabled = (bool) $settings->online_users_enabled;
                }
                if (!empty($settings->online_users_base)) {
                    $onlineUsersBase = (int) $settings->online_users_base;
                }
                if (!empty($settings->online_users_min)) {
                    $onlineUsersMin = (int) $settings->online_users_min;
                }
                if (!empty($settings->online_users_max)) {
                    $onlineUsersMax = (int) $settings->online_users_max;
                }
                if (!empty($settings->online_users_interval)) {
                    $onlineUsersInterval = (int) $settings->online_users_interval;
                }

                if (isset($settings->transaction_alerts_enabled)) {
                    $txAlertsEnabled = (bool) $settings->transaction_alerts_enabled;
                }
                if (!empty($settings->transaction_alerts_interval)) {
                    $txAlertsInterval = (int) $settings->transaction_alerts_interval;
                }
            }

            // Fetch active alerts from live_transaction_alerts table
            $alerts = collect();
            if (Schema::hasTable('live_transaction_alerts')) {
                $alerts = LiveTransactionAlert::where('status', 'active')
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('id', 'desc')
                    ->get()
                    ->map(function ($item) {
                        $action = strtolower($item->type) === 'deposit' ? 'deposited' : 'withdrew';
                        $formattedAmount = number_format((float) $item->amount);
                        $defaultMessage = "{$item->name} {$action} Rs. {$formattedAmount}";

                        return [
                            'id' => (int) $item->id,
                            'name' => (string) $item->name,
                            'type' => strtolower($item->type),
                            'amount' => (float) $item->amount,
                            'formatted_amount' => $formattedAmount,
                            'currency' => 'Rs.',
                            'time_ago' => (string) ($item->time_ago ?: 'just now'),
                            'message' => (string) ($item->custom_message ?: $defaultMessage),
                        ];
                    })->values();
            }

            // Fallback list if table is empty or not yet migrated
            if ($alerts->isEmpty()) {
                $fallback = [
                    ['id' => 1, 'name' => 'Ahmed Ali', 'type' => 'withdrawal', 'amount' => 5000, 'formatted_amount' => '5,000', 'currency' => 'Rs.', 'time_ago' => 'just now', 'message' => 'Ahmed Ali withdrew Rs. 5,000'],
                    ['id' => 2, 'name' => 'Muhammad Hassan', 'type' => 'deposit', 'amount' => 10000, 'formatted_amount' => '10,000', 'currency' => 'Rs.', 'time_ago' => '1 min ago', 'message' => 'Muhammad Hassan deposited Rs. 10,000'],
                    ['id' => 3, 'name' => 'Fatima Bibi', 'type' => 'withdrawal', 'amount' => 7500, 'formatted_amount' => '7,500', 'currency' => 'Rs.', 'time_ago' => '2 mins ago', 'message' => 'Fatima Bibi withdrew Rs. 7,500'],
                    ['id' => 4, 'name' => 'Ali Raza', 'type' => 'deposit', 'amount' => 3500, 'formatted_amount' => '3,500', 'currency' => 'Rs.', 'time_ago' => '3 mins ago', 'message' => 'Ali Raza deposited Rs. 3,500'],
                    ['id' => 5, 'name' => 'Zainab Noor', 'type' => 'withdrawal', 'amount' => 12000, 'formatted_amount' => '12,000', 'currency' => 'Rs.', 'time_ago' => '4 mins ago', 'message' => 'Zainab Noor withdrew Rs. 12,000'],
                ];
                $alerts = collect($fallback);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'online_users' => [
                        'enabled' => $onlineUsersEnabled,
                        'base_count' => $onlineUsersBase,
                        'min_count' => $onlineUsersMin,
                        'max_count' => $onlineUsersMax,
                        'interval_seconds' => $onlineUsersInterval,
                    ],
                    'transaction_alerts' => [
                        'enabled' => $txAlertsEnabled,
                        'interval_seconds' => $txAlertsInterval,
                        'alerts' => $alerts,
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Live Alerts API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
