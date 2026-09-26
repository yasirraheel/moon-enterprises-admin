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
            $withdrawalMinAmount = 2000;
            $withdrawalMaxAmount = 25000;
            $depositMinAmount = 1000;
            $depositMaxAmount = 20000;

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
                if (!empty($settings->withdrawal_min_amount)) {
                    $withdrawalMinAmount = (float) $settings->withdrawal_min_amount;
                }
                if (!empty($settings->withdrawal_max_amount)) {
                    $withdrawalMaxAmount = (float) $settings->withdrawal_max_amount;
                }
                if (!empty($settings->deposit_min_amount)) {
                    $depositMinAmount = (float) $settings->deposit_min_amount;
                }
                if (!empty($settings->deposit_max_amount)) {
                    $depositMaxAmount = (float) $settings->deposit_max_amount;
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

            // Calculate a realistic organic active online users count based on diurnal curve + time wave
            $now = now()->setTimezone('Asia/Karachi');
            $hour = (int) $now->format('G'); // 0 to 23
            $minute = (int) $now->format('i'); // 0 to 59
            $second = (int) $now->format('s'); // 0 to 59
            
            $range = max(0, $onlineUsersMax - $onlineUsersMin);
            if ($range > 0) {
                // Diurnal wave: Peak around 21:00 (9 PM PKT), valley around 05:00 (5 AM PKT)
                $timeOfDay = ($hour * 3600 + $minute * 60 + $second) / 86400.0;
                $diurnal = 0.5 + 0.35 * cos(2 * M_PI * ($timeOfDay - 0.875)); // 0.15 to 0.85
                
                // Micro variation wave (smoothly undulates over 10 minutes)
                $micro = 0.10 * sin(2 * M_PI * (($minute * 60 + $second) / 600.0));
                
                $fraction = max(0.08, min(0.92, $diurnal + $micro));
                $calculatedCurrent = (int) round($onlineUsersMin + ($range * $fraction));
            } else {
                $calculatedCurrent = $onlineUsersBase;
            }
            $calculatedCurrent = max($onlineUsersMin, min($onlineUsersMax, $calculatedCurrent));

            return response()->json([
                'success' => true,
                'data' => [
                    'online_users' => [
                        'enabled' => $onlineUsersEnabled,
                        'base_count' => $onlineUsersBase,
                        'current_count' => $calculatedCurrent,
                        'min_count' => $onlineUsersMin,
                        'max_count' => $onlineUsersMax,
                        'interval_seconds' => $onlineUsersInterval,
                    ],
                    'transaction_alerts' => [
                        'enabled' => $txAlertsEnabled,
                        'interval_seconds' => $txAlertsInterval,
                        'withdrawal_min_amount' => $withdrawalMinAmount,
                        'withdrawal_max_amount' => $withdrawalMaxAmount,
                        'deposit_min_amount' => $depositMinAmount,
                        'deposit_max_amount' => $depositMaxAmount,
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
