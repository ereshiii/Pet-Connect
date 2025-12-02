<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PushNotification;
use App\Services\FirebaseCloudMessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    protected FirebaseCloudMessagingService $fcmService;

    public function __construct(FirebaseCloudMessagingService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Send a notification to the authenticated user.
     */
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'data' => 'nullable|array',
            'priority' => 'nullable|in:high,normal',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->fcmService->sendToUser($request->user(), [
                'title' => $request->title,
                'body' => $request->body,
                'data' => $request->data ?? [],
                'priority' => $request->priority ?? 'normal',
            ]);

            // Log the notification
            if ($result['success']) {
                PushNotification::create([
                    'user_id' => $request->user()->id,
                    'title' => $request->title,
                    'body' => $request->body,
                    'data' => $request->data ?? [],
                    'type' => 'user_self',
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            }

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Notification sent successfully' : 'Failed to send notification',
                'sent_count' => $result['success_count'] ?? 0,
                'failed_count' => $result['failure_count'] ?? 0,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to send notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get notification history for the authenticated user.
     */
    public function history(Request $request)
    {
        $notifications = $request->user()
            ->pushNotifications()
            ->select(['id', 'title', 'body', 'type', 'status', 'is_read', 'sent_at', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, PushNotification $notification)
    {
        // Ensure the notification belongs to the authenticated user
        if ($notification->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Get unread notification count.
     */
    public function unreadCount(Request $request)
    {
        $count = $request->user()
            ->pushNotifications()
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'data' => ['unread_count' => $count]
        ]);
    }

    /**
     * Broadcast notification to all users (Admin only).
     */
    public function broadcast(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'data' => 'nullable|array',
            'target_account_types' => 'nullable|array',
            'target_account_types.*' => 'in:user,clinic,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $targetTypes = $request->target_account_types ?? ['user', 'clinic'];
            $users = User::whereIn('account_type', $targetTypes)->get();

            $totalSent = 0;
            $totalFailed = 0;

            foreach ($users as $user) {
                $result = $this->fcmService->sendToUser($user, [
                    'title' => $request->title,
                    'body' => $request->body,
                    'data' => array_merge($request->data ?? [], [
                        'type' => 'broadcast',
                        'sender' => 'admin'
                    ]),
                ]);

                if ($result['success']) {
                    $totalSent++;
                    
                    // Log the notification
                    PushNotification::create([
                        'user_id' => $user->id,
                        'title' => $request->title,
                        'body' => $request->body,
                        'data' => $request->data ?? [],
                        'type' => 'broadcast',
                        'status' => 'sent',
                        'sent_at' => now(),
                        'sender_id' => $request->user()->id,
                    ]);
                } else {
                    $totalFailed++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Broadcast completed',
                'data' => [
                    'total_users' => $users->count(),
                    'sent' => $totalSent,
                    'failed' => $totalFailed,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send broadcast',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Send notification to a specific user (Admin only).
     */
    public function sendToUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $targetUser = User::findOrFail($request->user_id);

            $result = $this->fcmService->sendToUser($targetUser, [
                'title' => $request->title,
                'body' => $request->body,
                'data' => array_merge($request->data ?? [], [
                    'type' => 'admin_direct',
                    'sender' => 'admin'
                ]),
            ]);

            if ($result['success']) {
                // Log the notification
                PushNotification::create([
                    'user_id' => $targetUser->id,
                    'title' => $request->title,
                    'body' => $request->body,
                    'data' => $request->data ?? [],
                    'type' => 'admin_direct',
                    'status' => 'sent',
                    'sent_at' => now(),
                    'sender_id' => $request->user()->id,
                ]);
            }

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Notification sent successfully' : 'Failed to send notification',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Send notification to all clinics (Admin only).
     */
    public function sendToClinic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'clinic_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($request->clinic_id) {
                // Send to specific clinic
                $clinic = User::where('id', $request->clinic_id)
                    ->where('account_type', 'clinic')
                    ->firstOrFail();
                
                $clinics = collect([$clinic]);
            } else {
                // Send to all clinics
                $clinics = User::where('account_type', 'clinic')->get();
            }

            $totalSent = 0;
            $totalFailed = 0;

            foreach ($clinics as $clinic) {
                $result = $this->fcmService->sendToUser($clinic, [
                    'title' => $request->title,
                    'body' => $request->body,
                    'data' => array_merge($request->data ?? [], [
                        'type' => 'clinic_notification',
                        'sender' => 'admin'
                    ]),
                ]);

                if ($result['success']) {
                    $totalSent++;
                    
                    // Log the notification
                    PushNotification::create([
                        'user_id' => $clinic->id,
                        'title' => $request->title,
                        'body' => $request->body,
                        'data' => $request->data ?? [],
                        'type' => 'clinic_notification',
                        'status' => 'sent',
                        'sent_at' => now(),
                        'sender_id' => $request->user()->id,
                    ]);
                } else {
                    $totalFailed++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Clinic notifications sent',
                'data' => [
                    'total_clinics' => $clinics->count(),
                    'sent' => $totalSent,
                    'failed' => $totalFailed,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send clinic notifications',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
