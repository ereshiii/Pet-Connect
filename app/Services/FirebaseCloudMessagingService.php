<?php

namespace App\Services;

use App\Models\User;
use App\Models\DeviceToken;
use App\Models\PushNotification;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FirebaseCloudMessagingService
{
    protected ?Messaging $messaging;
    
    public function __construct()
    {
        $this->messaging = app('firebase.messaging');
    }

    /**
     * Check if Firebase is properly configured.
     */
    public function isConfigured(): bool
    {
        return $this->messaging !== null;
    }

    /**
     * Send notification to a specific user.
     */
    public function sendToUser(User $user, array $notificationData): array
    {
        if (!$this->isConfigured()) {
            Log::warning('Firebase not configured, cannot send push notification');
            return ['success' => false, 'error' => 'Firebase not configured'];
        }

        $activeTokens = $user->activeDeviceTokens()->get();
        
        if ($activeTokens->isEmpty()) {
            Log::info("No active device tokens for user {$user->id}");
            return ['success' => false, 'error' => 'No active device tokens'];
        }

        return $this->sendToTokens($activeTokens->pluck('token')->toArray(), $notificationData, $user);
    }

    /**
     * Register a device token for a user.
     */
    public function registerDeviceToken(User $user, array $tokenData): DeviceToken
    {
        // Deactivate existing tokens with the same token value
        DeviceToken::where('token', $tokenData['token'])->update(['is_active' => false]);
        
        // Create new token record
        return $user->deviceTokens()->create([
            'token' => $tokenData['token'],
            'device_type' => $tokenData['device_type'] ?? 'web',
            'device_name' => $tokenData['device_name'] ?? null,
            'browser' => $tokenData['browser'] ?? null,
            'platform' => $tokenData['platform'] ?? null,
            'capabilities' => $tokenData['capabilities'] ?? [],
            'is_active' => true,
            'last_used_at' => now(),
        ]);
    }

    /**
     * Send notification to specific device tokens.
     */
    protected function sendToTokens(array $tokens, array $notificationData, ?User $user = null): array
    {
        // Implementation ready for when Firebase is configured
        if (!$this->isConfigured()) {
            Log::info('Firebase not configured, notification would be sent', [
                'tokens_count' => count($tokens),
                'notification' => $notificationData
            ]);
            
            return [
                'success' => true,
                'success_count' => count($tokens),
                'failure_count' => 0,
                'total_tokens' => count($tokens),
                'message' => 'Firebase not configured - notification simulated'
            ];
        }

        // Real implementation will go here when Firebase is set up
        return ['success' => false, 'error' => 'Implementation pending'];
    }
}
