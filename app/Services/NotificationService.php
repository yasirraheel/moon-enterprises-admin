<?php

namespace App\Services;

use App\Models\ManualNotification;
use App\Models\User;
use App\Models\UserDevices;
use App\Models\FcmNotificationSetting;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a public notification (manual)
     */
    public static function createPublicNotification($title, $message, $image = null)
    {
        Log::info('Creating Public Notification', ['title' => $title]);

        $notification = ManualNotification::create([
            'type' => 'public',
            'title' => $title,
            'message' => $message,
            'image' => $image,
            'is_active' => true
        ]);

        // Send to 'all' topic
        // Note: Clients must subscribe to 'all' topic
        Log::info('Sending Public Notification to topic: all');
        // Pass 'general' type so it respects general notification settings
        $result = FcmService::send('/topics/all', $title, $message, [], 'general');
        Log::info('Public Notification Send Result', ['success' => $result]);

        // Fallback: Also send to all registered tokens if topic fails or just to be safe (optional, can be heavy)
        // For now, relying on topic.

        return $notification;
    }

    /**
     * Create a user-specific notification
     */
    public static function createUserNotification($userId, $actionType, $title, $message, $metadata = null, $fcmType = null)
    {
        Log::info('Creating User Notification', [
            'user_id' => $userId,
            'action_type' => $actionType,
            'title' => $title
        ]);

        $notification = ManualNotification::create([
            'user_id' => $userId,
            'type' => 'user_specific',
            'action_type' => $actionType,
            'title' => $title,
            'message' => $message,
            'metadata' => $metadata,
            'is_active' => true
        ]);

        // Send to user's devices
        try {
            // Use the passed FCM type or fallback to action_type
            $notificationType = $fcmType ?? $actionType;

            $user = User::find($userId);
            if ($user) {
                 // Use sendToUser to handle token loading and validation consistently
                 // Also passes the notification type for settings check
                 \App\Services\FcmService::sendToUser($user, $title, $message, $metadata ?? [], $notificationType);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send FCM notification', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $notification;
    }

    /**
     * Notify user about balance addition
     */
    public static function notifyBalanceAdded($userId, $amount, $description = null)
    {
        $user = User::find($userId);
        $currentBalance = $user ? $user->balance : 0;
        $userName = $user ? ($user->full_name ?? $user->username) : 'User';

        $title = "💰 Balance Credited: Rs. " . number_format($amount, 2);
        $message = "Hello " . $userName . ", Rs. " . number_format($amount, 2) . " has been added to your wallet. ➕\n\nNew Balance: Rs. " . number_format($currentBalance, 2) . " 🏦";

        if ($description) {
            $message .= "\n\nNote: " . $description;
        }

        return self::createUserNotification(
            $userId,
            'balance_added',
            $title,
            $message,
            [
                'amount' => $amount,
                'description' => $description,
                'current_balance' => $currentBalance
            ],
            'financial'
        );
    }

    /**
     * Notify user about balance deduction
     */
    public static function notifyBalanceDeducted($userId, $amount, $description = null)
    {
        $user = User::find($userId);
        $currentBalance = $user ? $user->balance : 0;
        $userName = $user ? ($user->full_name ?? $user->username) : 'User';

        $title = "💸 Balance Debited: Rs. " . number_format($amount, 2);
        $message = "Hello " . $userName . ", Rs. " . number_format($amount, 2) . " has been deducted from your wallet. ➖\n\nNew Balance: Rs. " . number_format($currentBalance, 2) . " 🏦";

        if ($description) {
            $message .= "\n\nNote: " . $description;
        }

        return self::createUserNotification(
            $userId,
            'balance_deducted',
            $title,
            $message,
            [
                'amount' => $amount,
                'description' => $description,
                'current_balance' => $currentBalance
            ],
            'financial'
        );
    }

    /**
     * Notify user about deposit request submission
     */
    public static function notifyDepositRequested($userId, $amount, $depositId = null)
    {
        $title = 'Deposit Request Submitted';
        $message = "Your deposit request of Rs. " . number_format($amount, 2) . " has been submitted successfully. Please wait for admin approval.";

        return self::createUserNotification(
            $userId,
            'deposit_requested',
            $title,
            $message,
            [
                'amount' => $amount,
                'deposit_id' => $depositId
            ],
            'financial'
        );
    }

    /**
     * Notify user about deposit approval
     */
    public static function notifyDepositApproved($userId, $amount, $depositId = null, $adminRole = null)
    {
        $title = 'Deposit Approved';
        $message = "Your deposit of Rs. " . number_format($amount, 2) . " has been approved and added to your account.";

        if ($adminRole) {
            $message .= " Approved by: " . $adminRole;
        }

        return self::createUserNotification(
            $userId,
            'deposit_approved',
            $title,
            $message,
            [
                'amount' => $amount,
                'deposit_id' => $depositId,
                'admin_role' => $adminRole
            ],
            'financial'
        );
    }

    /**
     * Notify user about deposit rejection
     */
    public static function notifyDepositRejected($userId, $amount, $reason = null, $depositId = null, $adminRole = null)
    {
        $title = 'Deposit Rejected';
        $message = "Your deposit of Rs. " . number_format($amount, 2) . " has been rejected.";
        if ($reason) {
            $message .= " Reason: " . $reason;
        }

        if ($adminRole) {
            $message .= " Rejected by: " . $adminRole;
        }

        return self::createUserNotification(
            $userId,
            'deposit_rejected',
            $title,
            $message,
            [
                'amount' => $amount,
                'reason' => $reason,
                'deposit_id' => $depositId,
                'admin_role' => $adminRole
            ],
            'financial'
        );
    }

    /**
     * Notify user about withdrawal request submission
     */
    public static function notifyWithdrawalRequested($userId, $amount, $withdrawalId = null)
    {
        $title = 'Withdrawal Request Submitted';
        $message = "Your withdrawal request of Rs. " . number_format($amount, 2) . " has been submitted and amount has been reserved from your balance. It will be reviewed by admin.";

        return self::createUserNotification(
            $userId,
            'withdrawal_requested',
            $title,
            $message,
            [
                'amount' => $amount,
                'withdrawal_id' => $withdrawalId
            ],
            'financial'
        );
    }

    /**
     * Notify user about withdrawal approval
     */
    public static function notifyWithdrawalApproved($userId, $amount, $withdrawalId = null, $adminRole = null)
    {
        $title = 'Withdrawal Approved';
        $message = "Your withdrawal request of Rs. " . number_format($amount, 2) . " has been approved and processed.";

        if ($adminRole) {
            $message .= " Approved by: " . $adminRole;
        }

        return self::createUserNotification(
            $userId,
            'withdrawal_approved',
            $title,
            $message,
            [
                'amount' => $amount,
                'withdrawal_id' => $withdrawalId,
                'admin_role' => $adminRole
            ],
            'financial'
        );
    }

    /**
     * Notify user about withdrawal rejection
     */
    public static function notifyWithdrawalRejected($userId, $amount, $reason = null, $withdrawalId = null, $adminRole = null)
    {
        $title = 'Withdrawal Rejected';
        $message = "Your withdrawal request of Rs. " . number_format($amount, 2) . " has been rejected.";
        if ($reason) {
            $message .= " Reason: " . $reason;
        }

        if ($adminRole) {
            $message .= " Rejected by: " . $adminRole;
        }

        return self::createUserNotification(
            $userId,
            'withdrawal_rejected',
            $title,
            $message,
            [
                'amount' => $amount,
                'reason' => $reason,
                'withdrawal_id' => $withdrawalId,
                'admin_role' => $adminRole
            ],
            'financial'
        );
    }

    /**
     * Notify user about order placement
     */
    public static function notifyOrderPlaced($userId, $amount, $gameName, $orderId = null, $commissionAmount = 0)
    {
        // Check if order_placed notification is enabled
        $setting = FcmNotificationSetting::where('notification_type', 'order_placed')->first();
        if ($setting && !$setting->is_enabled) {
            return null;
        }

        $title = 'Order Placed';
        $message = "Your order for " . $gameName . " worth Rs. " . number_format($amount, 2) . " has been placed successfully.";

        if ($commissionAmount > 0) {
            $message .= " (Commission Saved: Rs. " . number_format($commissionAmount, 2) . ")";
        }

        return self::createUserNotification(
            $userId,
            'order_placed',
            $title,
            $message,
            [
                'amount' => $amount,
                'game_name' => $gameName,
                'order_id' => $orderId,
                'commission_amount' => $commissionAmount
            ],
            'orders'
        );
    }

    /**
     * Notify user about order status update
     */
    public static function notifyOrderStatusUpdated($userId, $status, $gameName, $orderDetails = [])
    {
        $orderId = $orderDetails['id'] ?? null;
        $bondName = $orderDetails['bond_name'] ?? '';
        $rttp = $orderDetails['rttp'] ?? '';
        $first = $orderDetails['first'] ?? '';
        $second = $orderDetails['second'] ?? '';

        $detailsStr = "";
        if ($bondName) $detailsStr .= "\n🎫 Bond: " . $bondName;
        if ($rttp) $detailsStr .= "\n💰 RTTP: " . $rttp;
        if ($first) $detailsStr .= "\n🥇 First: " . $first;
        if ($second) $detailsStr .= "\n🥈 Second: " . $second;

        $statusMessages = [
            'OK' => [
                'title' => 'Order Confirmed',
                'message' => 'Your order for ' . $gameName . ' has been confirmed.' . $detailsStr,
            ],
            'first_win' => [
                'title' => 'First Win! 🎉',
                'message' => 'Congratulations! Your order for ' . $gameName . ' has won First Win!' . $detailsStr,
            ],
            'second_win' => [
                'title' => 'Second Win! 🎉',
                'message' => 'Congratulations! Your order for ' . $gameName . ' has won Second Win!' . $detailsStr,
            ],
            'rejected' => [
                'title' => 'Order Rejected',
                'message' => 'Your order for ' . $gameName . ' has been rejected and refunded.' . $detailsStr,
            ]
        ];

        $statusInfo = $statusMessages[$status] ?? [
            'title' => 'Order Status Updated',
            'message' => 'Your order for ' . $gameName . ' status has been updated.' . $detailsStr,
        ];

        return self::createUserNotification(
            $userId,
            'order_status_updated',
            $statusInfo['title'],
            $statusInfo['message'],
            array_merge([
                'order_id' => $orderId,
                'game_name' => $gameName,
                'status' => $status
            ], $orderDetails),
            'orders'
        );
    }

    /**
     * Notify user about paid service purchase
     */
    public static function notifyPaidServicePurchased($userId, $amount, $serviceName, $saleId = null)
    {
        $title = 'Service Purchased';
        $message = "You have successfully purchased " . $serviceName . " for Rs. " . number_format($amount, 2) . ".";

        return self::createUserNotification(
            $userId,
            'paid_service_purchased',
            $title,
            $message,
            [
                'amount' => $amount,
                'service_name' => $serviceName,
                'sale_id' => $saleId
            ],
            'services_dealership'
        );
    }

    /**
     * Get notifications for a specific user (both public and user-specific)
     */
    public static function getUserNotifications($userId, $limit = 20)
    {
        Log::info('NotificationService::getUserNotifications called', [
            'user_id' => $userId,
            'limit' => $limit
        ]);

        $query = ManualNotification::where('is_active', true)
            ->where(function ($query) use ($userId) {
                $query->where('type', 'public')
                      ->orWhere(function ($q) use ($userId) {
                          $q->where('type', 'user_specific')
                            ->where('user_id', $userId);
                      });
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        // Log the SQL query
        Log::info('NotificationService SQL Query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        $notifications = $query->get();

        // Get read status for public notifications
        $readPublicNotificationIds = \DB::table('user_read_public_notifications')
            ->where('user_id', $userId)
            ->pluck('notification_id')
            ->toArray();

        // Add read status to notifications
        $notifications->each(function ($notification) use ($userId, $readPublicNotificationIds) {
            if ($notification->type === 'public') {
                // For public notifications, check if user has read it
                $notification->is_read = in_array($notification->id, $readPublicNotificationIds);
                $notification->read_at = $notification->is_read ?
                    \DB::table('user_read_public_notifications')
                        ->where('user_id', $userId)
                        ->where('notification_id', $notification->id)
                        ->value('read_at') : null;
            } else {
                // For user-specific notifications, use the read_at field directly
                $notification->is_read = $notification->read_at !== null;
            }
        });

        Log::info('NotificationService Query Results', [
            'user_id' => $userId,
            'total_found' => $notifications->count(),
            'notifications' => $notifications->map(function($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'user_id' => $n->user_id,
                    'action_type' => $n->action_type,
                    'title' => $n->title,
                    'is_active' => $n->is_active,
                    'is_read' => $n->is_read ?? false
                ];
            })->toArray()
        ]);

        return $notifications;
    }

    /**
     * Get notification count for a specific user (only unread notifications)
     */
    public static function getUserNotificationCount($userId)
    {
        // Get user-specific unread notifications
        $userSpecificUnread = ManualNotification::where('is_active', true)
            ->where('type', 'user_specific')
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();

        // Get public notifications that this user hasn't read
        // We'll use a separate table to track which public notifications each user has read
        $readPublicNotificationIds = \DB::table('user_read_public_notifications')
            ->where('user_id', $userId)
            ->pluck('notification_id')
            ->toArray();

        $publicUnread = ManualNotification::where('is_active', true)
            ->where('type', 'public')
            ->whereNotIn('id', $readPublicNotificationIds)
            ->count();

        return $userSpecificUnread + $publicUnread;
    }

    /**
     * Notify user when dealership request is submitted
     */
    public static function notifyDealershipRequested($userId)
    {
        $user = User::find($userId);
        $userName = $user ? ($user->full_name ?? $user->username) : 'User';

        $title = 'Dealership Request Submitted';
        $message = "Hello " . $userName . ", Your dealership application has been submitted successfully. Please wait for admin approval.";

        return self::createUserNotification(
            $userId,
            'dealership_requested',
            $title,
            $message,
            [
                'type' => 'dealership',
                'status' => 'pending'
            ],
            'services_dealership'
        );
    }

    /**
     * Notify user when dealership request is approved
     */
    public static function notifyDealershipApproved($userId, $commission = null)
    {
        $user = User::find($userId);
        $userName = $user ? ($user->full_name ?? $user->username) : 'User';

        $title = '🎉 Dealership Approved!';
        $message = "Hello " . $userName . ", Congratulations! Your dealership application has been approved. You can now access dealer features. ✅";

        if ($commission !== null) {
            $message .= "\n\nYour commission is set to " . $commission . "%. 💰";
        }

        return self::createUserNotification(
            $userId,
            'dealership_approved',
            $title,
            $message,
            [
                'commission' => $commission,
                'type' => 'dealership',
                'status' => 'approved'
            ],
            'services_dealership'
        );
    }

    /**
     * Notify user when dealership status or commission is updated
     */
    public static function notifyDealershipUpdated($userId, $status, $commission = null, $isCommissionOnly = false)
    {
        $user = User::find($userId);
        $userName = $user ? ($user->full_name ?? $user->username) : 'User';

        $title = 'Dealership Status Update';
        $message = "Hello " . $userName . ", ";

        if ($isCommissionOnly) {
            $title = 'Commission Rate Updated';
            $message = "Hello " . $userName . ", Your dealership commission has been updated to " . $commission . "%. 💰";
        } else {
            switch (strtolower($status)) {
                case 'na':
                    $title = 'Dealership Status Reset';
                    $message .= "Your dealership privileges have been revoked, and your account is now a standard user account.";
                    break;
                case 'pending':
                    $title = 'Dealership Application Under Review';
                    $message .= "Your dealership status has been set to Pending. We are reviewing your account.";
                    break;
                case 'approved':
                    $title = '🎉 Dealership Status Active';
                    $message .= "Your dealership status has been updated to Approved. You now have access to dealer features.";
                    if ($commission !== null) {
                        $message .= "\n\nCommission Rate: " . $commission . "%";
                    }
                    break;
                case 'rejected':
                    $title = 'Dealership Status Update';
                    $message .= "Your dealership status has been set to Rejected.";
                    break;
                default:
                    $formattedStatus = strtoupper($status) === 'NA' ? 'N/A' : ucfirst($status);
                    $message .= "Your dealership status has been updated to: " . $formattedStatus . ".";
                    break;
            }
        }

        return self::createUserNotification(
            $userId,
            'dealership_updated',
            $title,
            $message,
            [
                'status' => $status,
                'commission' => $commission,
                'is_commission_only' => $isCommissionOnly
            ],
            'services_dealership'
        );
    }

    /**
     * Notify user when dealership request is rejected
     */
    public static function notifyDealershipRejected($userId, $reason)
    {
        $user = User::find($userId);
        $userName = $user ? ($user->full_name ?? $user->username) : 'User';

        $title = '❌ Dealership Request Rejected';
        $message = "Hello " . $userName . ", Your dealership application has been rejected.";

        if ($reason) {
            $message .= "\n\nReason: " . $reason;
        }

        return self::createUserNotification(
            $userId,
            'dealership_rejected',
            $title,
            $message,
            [
                'reason' => $reason,
                'type' => 'dealership',
                'status' => 'rejected'
            ],
            'services_dealership'
        );
    }
}
