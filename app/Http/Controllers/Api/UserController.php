<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUserBalance()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get balance from user table
            $balance = $user->balance ?? 0;

            return response()->json([
                'success' => true,
                'user_balance' => $balance
            ]);

        } catch (\Exception $e) {
            \Log::error('Get User Balance Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error loading balance',
                'user_balance' => 0
            ], 500);
        }
    }

    public function resetCommission(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get current commission before reset for notification
            $currentCommission = $user->commission_earned;

            // Reset commission earned to 0
            $user->commission_earned = 0;
            $user->save();

            // Send Notification (App + FCM)
            try {
                $title = "Commission Reset";
                $message = "Your commission balance of " . number_format($currentCommission, 2) . " has been reset successfully.";

                // 1. Create ManualNotification record (holds the content)
                $manualNotification = \App\Models\ManualNotification::create([
                    'user_id' => $user->id,
                    'type' => 'user_specific',
                    'action_type' => 'commission_reset',
                    'title' => $title,
                    'message' => $message,
                    'is_active' => true
                ]);

                // 2. Create In-App Notification (links user to the content)
                \App\Models\Notifications::create([
                    'destination' => $user->id,
                    'author' => 1, // System/Admin ID
                    'type' => 'manual', // Must be 'manual' to resolve to ManualNotification
                    'target' => $manualNotification->id,
                    'created_at' => now()
                ]);

                // 3. Send FCM Notification
                \App\Services\FcmService::sendToUser(
                    $user,
                    $title,
                    $message,
                    [
                        'type' => 'commission_reset',
                        'amount' => $currentCommission
                    ],
                    'financial'
                );

            } catch (\Exception $e) {
                \Log::error('Commission Reset Notification Error: ' . $e->getMessage());
                // Continue execution - notification failure shouldn't fail the reset
            }

            return response()->json([
                'success' => true,
                'message' => 'Commission reset successfully',
                'commission_earned' => 0,
                'previous_commission' => $currentCommission
            ]);

        } catch (\Exception $e) {
            \Log::error('Reset Commission Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset commission'
            ], 500);
        }
    }
}
