<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\WebPushConfig;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        try {
            // Option 1: Use service account file (for local development)
            if (config('firebase.credentials.path') && file_exists(config('firebase.credentials.path'))) {
                $factory = (new Factory)->withServiceAccount(config('firebase.credentials.path'));
            }
            // Option 2: Use environment variables (for Railway deployment)
            elseif (config('firebase.credentials.array.project_id')) {
                $factory = (new Factory)->withServiceAccount(config('firebase.credentials.array'));
            } else {
                throw new \Exception('Firebase credentials not configured');
            }

            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Firebase initialization failed: ' . $e->getMessage());
            $this->messaging = null;
        }
    }

    /**
     * Send notification to a specific user (all active devices)
     */
    public function sendToUser(User $user, string $title, string $body, array $data = [], ?string $icon = null)
    {
        if (!$this->messaging) {
            return false;
        }

        // Get all active device tokens for the user
        $deviceTokens = $user->deviceTokens()->active()->pluck('token')->toArray();
        
        if (empty($deviceTokens)) {
            return false;
        }

        try {
            $notification = Notification::create($title, $body);
            
            if ($icon) {
                $notification = $notification->withImageUrl($icon);
            }

            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data)
                ->withWebPushConfig(
                    WebPushConfig::fromArray([
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                            'icon' => $icon ?? url('/icons/notification-icon.png'),
                            'badge' => url('/icons/badge-icon.png'),
                            'click_action' => $data['url'] ?? url('/'),
                            'requireInteraction' => $data['requireInteraction'] ?? false,
                        ],
                        'fcm_options' => [
                            'link' => $data['url'] ?? url('/'),
                        ],
                    ])
                );

            $this->messaging->sendMulticast($message, $deviceTokens);
            
            Log::info("Firebase notification sent to user {$user->id} on " . count($deviceTokens) . " devices: {$title}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send Firebase notification to user {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to multiple users
     */
    public function sendToMultipleUsers(array $users, string $title, string $body, array $data = [])
    {
        if (!$this->messaging) {
            return false;
        }

        $userIds = collect($users)->pluck('id')->toArray();
        
        $tokens = \App\Models\DeviceToken::whereIn('user_id', $userIds)
            ->active()
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return false;
        }

        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->sendMulticast($message, $tokens);
            
            Log::info("Firebase notification sent to " . count($tokens) . " users: {$title}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send Firebase multicast notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to a topic
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = [])
    {
        if (!$this->messaging) {
            return false;
        }

        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($message);
            
            Log::info("Firebase notification sent to topic '{$topic}': {$title}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send Firebase topic notification to '{$topic}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Subscribe user to a topic (all devices)
     */
    public function subscribeToTopic(User $user, string $topic)
    {
        if (!$this->messaging) {
            return false;
        }

        $tokens = $user->deviceTokens()->active()->pluck('token')->toArray();
        
        if (empty($tokens)) {
            return false;
        }

        try {
            $this->messaging->subscribeToTopic($topic, $tokens);
            Log::info("User {$user->id} subscribed to topic '{$topic}' on " . count($tokens) . " devices");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to subscribe user {$user->id} to topic '{$topic}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Unsubscribe user from a topic (all devices)
     */
    public function unsubscribeFromTopic(User $user, string $topic)
    {
        if (!$this->messaging) {
            return false;
        }

        $tokens = $user->deviceTokens()->active()->pluck('token')->toArray();
        
        if (empty($tokens)) {
            return false;
        }

        try {
            $this->messaging->unsubscribeFromTopic($topic, $tokens);
            Log::info("User {$user->id} unsubscribed from topic '{$topic}' on " . count($tokens) . " devices");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to unsubscribe user {$user->id} from topic '{$topic}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send appointment notification
     */
    public function sendAppointmentNotification(User $user, string $type, array $appointmentData)
    {
        $titles = [
            'created' => '🗓️ Appointment Booked',
            'confirmed' => '✅ Appointment Confirmed',
            'reminder' => '⏰ Appointment Reminder',
            'cancelled' => '❌ Appointment Cancelled',
            'completed' => '✅ Appointment Completed',
        ];

        $title = $titles[$type] ?? 'Appointment Update';
        $body = $this->getAppointmentMessage($type, $appointmentData);

        return $this->sendToUser(
            $user,
            $title,
            $body,
            [
                'type' => 'appointment',
                'appointment_id' => (string) $appointmentData['id'],
                'url' => url("/appointments/{$appointmentData['id']}"),
                'requireInteraction' => in_array($type, ['created', 'confirmed', 'reminder']),
            ],
            url('/icons/appointment-icon.png')
        );
    }

    private function getAppointmentMessage(string $type, array $data): string
    {
        return match ($type) {
            'created' => "Your appointment with {$data['clinic_name']} has been booked for {$data['date']} at {$data['time']}",
            'confirmed' => "{$data['clinic_name']} confirmed your appointment on {$data['date']} at {$data['time']}",
            'reminder' => "Reminder: You have an appointment tomorrow at {$data['time']} with {$data['clinic_name']}",
            'cancelled' => "Your appointment with {$data['clinic_name']} on {$data['date']} has been cancelled",
            'completed' => "Your appointment with {$data['clinic_name']} is complete. How was your visit?",
            default => "Appointment update from {$data['clinic_name']}",
        };
    }
}
