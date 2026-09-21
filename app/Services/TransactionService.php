<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Services\NotificationService;

class TransactionService
{
    /**
     * Log a transaction
     */
    public static function logTransaction(
        $userId,
        $transactionAmount,
        $type, // 'credit' or 'debit'
        $transactionType, // 'deposit', 'withdrawal', 'paid_service', etc.
        $description = null,
        $referenceId = null,
        $referenceType = null,
        $metadata = null
    ) {
        // Get current user balance
        $user = User::find($userId);
        if (!$user) {
            throw new \Exception('User not found');
        }

        $currentBalance = $user->balance;

        // Calculate remaining balance
        $remainingBalance = $type === 'credit'
            ? $currentBalance + $transactionAmount
            : $currentBalance - $transactionAmount;

        // Create transaction record
        $transaction = Transaction::create([
            'user_id' => $userId,
            'current_balance' => $currentBalance,
            'transaction_amount' => $transactionAmount,
            'type' => $type,
            'remaining_balance' => $remainingBalance,
            'transaction_type' => $transactionType,
            'description' => $description,
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'metadata' => $metadata
        ]);

        // Update user balance
        $user->update(['balance' => $remainingBalance]);

        return $transaction;
    }

    /**
     * Log deposit transaction
     */
    public static function logDeposit($userId, $amount, $depositId, $description = 'Deposit approved', $adminRole = null)
    {
        $transaction = self::logTransaction(
            $userId,
            $amount,
            'credit',
            'deposit',
            $description,
            $depositId,
            'Deposits'
        );

        // Create notification for user
        NotificationService::notifyDepositApproved($userId, $amount, $depositId, $adminRole);

        return $transaction;
    }

    /**
     * Log withdrawal transaction
     */
    public static function logWithdrawal($userId, $amount, $withdrawalId, $description = 'Withdrawal processed', $adminRole = null)
    {
        $transaction = self::logTransaction(
            $userId,
            $amount,
            'debit',
            'withdrawal',
            $description,
            $withdrawalId,
            'Withdrawals'
        );

        // Create notification for user
        NotificationService::notifyWithdrawalApproved($userId, $amount, $withdrawalId, $adminRole);

        return $transaction;
    }

    /**
     * Log withdrawal request (deduct balance immediately)
     */
    public static function logWithdrawalRequest($userId, $amount, $withdrawalId, $description = 'Withdrawal request created')
    {
        $transaction = self::logTransaction(
            $userId,
            $amount,
            'debit',
            'withdrawal_request',
            $description,
            $withdrawalId,
            'Withdrawals'
        );

        // Create notification for user
        NotificationService::notifyWithdrawalRequested($userId, $amount, $withdrawalId);

        return $transaction;
    }

    /**
     * Log paid service purchase
     */
    public static function logPaidService($userId, $amount, $saleId, $serviceTitle, $description = 'Paid service purchased')
    {
        $transaction = self::logTransaction(
            $userId,
            $amount,
            'debit',
            'paid_service',
            $description,
            $saleId,
            'PaidServiceSale',
            ['service_title' => $serviceTitle]
        );

        // Create notification for user
        NotificationService::notifyPaidServicePurchased($userId, $amount, $serviceTitle, $saleId);

        return $transaction;
    }

    /**
     * Log order placement
     */
    public static function logOrder($userId, $amount, $orderId, $gameName, $description = 'Order placed', $commissionAmount = 0)
    {
        $transaction = self::logTransaction(
            $userId,
            $amount,
            'debit',
            'order_placed',
            $description,
            $orderId,
            'Orders',
            ['game_name' => $gameName, 'commission' => $commissionAmount]
        );

        // Create notification for user
        NotificationService::notifyOrderPlaced($userId, $amount, $gameName, $orderId, $commissionAmount);

        return $transaction;
    }

    /**
     * Log dealer commission credit
     */
    public static function logCommission($userId, $amount, $orderId, $gameName, $description = 'Dealer commission earned')
    {
        $transaction = self::logTransaction(
            $userId,
            $amount,
            'credit',
            'dealer_commission',
            $description,
            $orderId,
            'Orders',
            ['game_name' => $gameName]
        );

        // We don't send a separate notification for commission to avoid spam,
        // as the order placed notification already mentions the saving.
        // But if needed, we can add one here.

        return $transaction;
    }

    /**
     * Log admin credit
     */
    public static function logAdminCredit($userId, $amount, $description = 'Admin credit')
    {
        $transaction = self::logTransaction(
            $userId,
            $amount,
            'credit',
            'admin_credit',
            $description
        );

        // Create notification for user
        NotificationService::notifyBalanceAdded($userId, $amount, $description);

        return $transaction;
    }

    /**
     * Log admin debit
     */
    public static function logAdminDebit($userId, $amount, $description = 'Admin debit')
    {
        $transaction = self::logTransaction(
            $userId,
            $amount,
            'debit',
            'admin_debit',
            $description
        );

        // Create notification for user
        NotificationService::notifyBalanceDeducted($userId, $amount, $description);

        return $transaction;
    }

    /**
     * Log withdrawal approval (no balance change - already deducted)
     */
    public static function logWithdrawalApproval($userId, $amount, $withdrawalId, $description = 'Withdrawal approved', $adminRole = null)
    {
        // Log approval without affecting balance (amount was already deducted during request)
        $transaction = Transaction::create([
            'user_id' => $userId,
            'current_balance' => User::find($userId)->balance,
            'transaction_amount' => $amount,
            'type' => 'info', // Just informational, no balance change
            'remaining_balance' => User::find($userId)->balance,
            'transaction_type' => 'withdrawal_approved',
            'description' => $description,
            'reference_id' => $withdrawalId,
            'reference_type' => 'Withdrawals'
        ]);

        // Create notification for user
        NotificationService::notifyWithdrawalApproved($userId, $amount, $withdrawalId, $adminRole);

        return $transaction;
    }

    /**
     * Log signup bonus
     */
    public static function logSignupBonus($userId, $amount, $description = 'Signup bonus')
    {
        return self::logTransaction(
            $userId,
            $amount,
            'credit',
            'signup_bonus',
            $description
        );
    }

    /**
     * Log refund
     */
    public static function logRefund($userId, $amount, $referenceId, $referenceType, $description = 'Refund processed')
    {
        $transaction = self::logTransaction(
            $userId,
            $amount,
            'credit',
            'refund',
            $description,
            $referenceId,
            $referenceType
        );

        // Create notification for user
        NotificationService::createUserNotification(
            $userId,
            'refund_processed',
            'Refund Processed',
            'A refund of Rs. ' . number_format($amount, 2) . ' has been processed to your account.',
            [
                'amount' => $amount,
                'reference_id' => $referenceId,
                'reference_type' => $referenceType
            ]
        );

        return $transaction;
    }

    /**
     * Log deposit rejection (no transaction, just notification)
     */
    public static function logDepositRejection($userId, $amount, $depositId, $reason = null, $adminRole = null)
    {
        // Create notification for user
        NotificationService::notifyDepositRejected($userId, $amount, $reason, $depositId, $adminRole);
    }

    /**
     * Log withdrawal rejection and refund balance
     */
    public static function logWithdrawalRejection($userId, $amount, $withdrawalId, $reason = null, $adminRole = null)
    {
        // Refund the amount back to user since withdrawal was rejected
        $transaction = self::logTransaction(
            $userId,
            $amount,
            'credit', // Credit back the amount
            'withdrawal_rejected',
            'Withdrawal rejected - Amount refunded' . ($reason ? ': ' . $reason : ''),
            $withdrawalId,
            'Withdrawals'
        );

        // Create notification for user
        NotificationService::notifyWithdrawalRejected($userId, $amount, $withdrawalId, $reason, $adminRole);

        return $transaction;
    }
}
