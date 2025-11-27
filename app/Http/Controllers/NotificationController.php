<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Return Inertia view for web requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $this->notificationService->getUnreadCount($user),
            ]);
        }

        return \Inertia\Inertia::render('Notifications', [
            'initialNotifications' => $notifications,
            'unreadCount' => $this->notificationService->getUnreadCount($user),
        ]);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount(Request $request)
    {
        return response()->json([
            'count' => $this->notificationService->getUnreadCount($request->user()),
        ]);
    }

    /**
     * Get recent notifications (for dropdown).
     */
    public function recent(Request $request)
    {
        $user = $request->user();
        
        $notifications = $this->notificationService->getRecentNotifications($user, 10);
        
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $this->notificationService->getUnreadCount($user),
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        // Ensure user owns the notification
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => $notification->fresh(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $this->notificationService->markAllAsRead($request->user());

        return response()->json([
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, Notification $notification)
    {
        // Ensure user owns the notification
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted',
        ]);
    }
}
