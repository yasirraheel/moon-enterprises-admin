<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FcmService
{
    protected static $messaging;

    /**
     * Get Firebase Messaging instance
     */
    public static function getMessaging()
    {
        if (!self::$messaging) {
            $credentialsPath = storage_path('app/firebase_credentials.json');

            if (!file_exists($credentialsPath)) {
                Log::error('Firebase Credentials file not found at: ' . $credentialsPath);
                return null;
            }

            try {
                $factory = (new Factory)->withServiceAccount($credentialsPath);
                self::$messaging = $factory->createMessaging();
            } catch (\Exception $e) {
                Log::error('Firebase Init Error: ' . $e->getMessage());
                return null;
            }
        }
        return self::$messaging;
    }

    /**
     * Check if notification type is enabled
     * 
     * @param string $notificationType
     * @return bool
     */
    public static function isNotificationEnabled($notificationType)
    {
        if (empty($notificationType)) {
            return true; // Default to enabled if no type specified
        }

        try {
            $setting = \App\Models\FcmNotificationSetting::where('notification_type', $notificationType)->first();
            if ($setting) {
                return $setting->is_enabled;
            }
        } catch (\Exception $e) {
            // Table might not exist yet or connection error
            Log::warning('FCM Setting Check Failed: ' . $e->getMessage());
        }

        return true; // Default to true if setting not found
    }

    /**
     * Send Push Notification to a specific User
     *
     * @param \App\Models\User $user User model instance
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @param string|null $notificationType Optional notification type key to check against settings
     * @return bool
     */
    public static function sendToUser($user, $title, $body, $data = [], $notificationType = null)
    {
        if (!$user) {
            return false;
        }

        if ($notificationType && !self::isNotificationEnabled($notificationType)) {
            Log::info("FCM: Notification type '{$notificationType}' is disabled. Skipping for user {$user->id}.");
            return false;
        }

        // Ensure user has relation loaded or load it
        if (!$user->relationLoaded('fcmTokens')) {
            $user->load('fcmTokens');
        }

        $tokens = $user->fcmTokens->pluck('fcm_token')->toArray();

        // Include legacy fcm_token from users table if it exists and is not already in the list
        if (!empty($user->fcm_token) && !in_array($user->fcm_token, $tokens)) {
            $tokens[] = $user->fcm_token;
        }
        
        if (empty($tokens)) {
            Log::warning("FCM: No tokens found for user {$user->id}");
            return false;
        }

        return self::send($tokens, $title, $body, $data, $notificationType);
    }

    /**
     * Send Push Notification via Firebase Admin SDK
     *
     * @param string|array $tokens Single token or array of tokens
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @param string|null $notificationType Optional notification type key to check against settings
     * @return bool
     */
    public static function send($tokens, $title, $body, $data = [], $notificationType = null)
    {
        if ($notificationType && !self::isNotificationEnabled($notificationType)) {
            Log::info("FCM: Notification type '{$notificationType}' is disabled. Skipping send.");
            return false;
        }

        $messaging = self::getMessaging();
        if (!$messaging) {
            return false;
        }

        if (is_string($tokens)) {
            $tokens = [$tokens];
        }

        if (empty($tokens)) {
            return false;
        }

        $notification = Notification::create($title, $body);

        $successCount = 0;
        $failureCount = 0;

        Log::info('FCM: Starting batch send', ['count' => count($tokens)]);

        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::new();

                // Check if topic
                if (str_starts_with($token, '/topics/')) {
                    $target = str_replace('/topics/', '', $token);
                    $message = $message->withTarget('topic', $target);
                } else {
                    $message = $message->withTarget('token', $token);
                }

                $message = $message->withNotification($notification)
                                   ->withData($data)
                                   ->withAndroidConfig([
                                       'priority' => 'high',
                                       'ttl' => '2419200s', // 4 weeks (max retention)
                                       'notification' => [
                                           'sound' => 'default',
                                           'icon' => 'ic_notification',
                                           'color' => '#f4511e',
                                           'click_action' => 'OPEN_ACTIVITY_1'
                                       ]
                                   ]);

                $messaging->send($message);
                $successCount++;
                Log::info('FCM Send Success', ['target' => $token]);

            } catch (\Exception $e) {
                $failureCount++;
                Log::error('FCM Send Failed', ['target' => $token, 'error' => $e->getMessage()]);
            }
        }

        Log::info("FCM Batch Send Result: Success={$successCount}, Failed={$failureCount}");
        return $successCount > 0;
    }
}
