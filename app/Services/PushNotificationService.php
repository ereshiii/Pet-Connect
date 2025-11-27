<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected $fcmService;

    public function __construct(FirebaseCloudMessagingService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Send push notification to user.
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): bool
    {
        // Check if user has FCM token
        if (!$user->fcm_token) {
            Log::info("User {$user->id} has no FCM token, skipping push notification");
            return false;
        }

        // Check if user has opted out of push notifications
        if ($user->profile && isset($user->profile->push_notifications_enabled) && !$user->profile->push_notifications_enabled) {
            Log::info("User {$user->id} has disabled push notifications");
            return false;
        }

        try {
            // Send via Firebase Cloud Messaging
            $result = $this->fcmService->sendNotification(
                $user->fcm_token,
                $title,
                $body,
                $data
            );

            Log::info("Push notification sent to user {$user->id}", [
                'title' => $title,
                'result' => $result
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send push notification to user {$user->id}", [
                'error' => $e->getMessage(),
                'title' => $title
            ]);
            return false;
        }
    }

    /**
     * Send push notification for a database notification.
     */
    public function sendForNotification(Notification $notification): bool
    {
        $user = $notification->user;
        
        if (!$user) {
            return false;
        }

        return $this->sendToUser(
            $user,
            $notification->title,
            $notification->message,
            [
                'notification_id' => $notification->id,
                'type' => $notification->type,
                'priority' => $notification->priority,
                ...$notification->data ?? []
            ]
        );
    }

    /**
     * Send push notification to multiple users.
     */
    public function sendToMultipleUsers(array $userIds, string $title, string $body, array $data = []): array
    {
        $results = [];
        $users = User::whereIn('id', $userIds)->whereNotNull('fcm_token')->get();

        foreach ($users as $user) {
            $results[$user->id] = $this->sendToUser($user, $title, $body, $data);
        }

        return $results;
    }

    /**
     * Subscribe user to push notifications (save FCM token).
     */
    public function subscribe(User $user, string $fcmToken): bool
    {
        try {
            $user->update(['fcm_token' => $fcmToken]);
            
            // Enable push notifications in profile if exists
            if ($user->profile) {
                $user->profile->update(['push_notifications_enabled' => true]);
            }

            Log::info("User {$user->id} subscribed to push notifications");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to subscribe user {$user->id} to push notifications", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Unsubscribe user from push notifications.
     */
    public function unsubscribe(User $user): bool
    {
        try {
            $user->update(['fcm_token' => null]);
            
            // Disable push notifications in profile if exists
            if ($user->profile) {
                $user->profile->update(['push_notifications_enabled' => false]);
            }

            Log::info("User {$user->id} unsubscribed from push notifications");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to unsubscribe user {$user->id} from push notifications", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if user is subscribed to push notifications.
     */
    public function isSubscribed(User $user): bool
    {
        return !empty($user->fcm_token) && 
               (!$user->profile || ($user->profile->push_notifications_enabled ?? true));
    }
}
