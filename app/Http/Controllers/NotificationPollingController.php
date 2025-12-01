<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationPollingController extends Controller
{
    /**
     * Get unread notification count and latest notifications
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'unread_count' => 0,
                'latest' => [],
            ]);
        }

        // Get unread count
        $unreadCount = $user->notifications()
            ->whereNull('read_at')
            ->count();

        // Get latest 5 unread notifications
        $latestNotifications = $user->notifications()
            ->whereNull('read_at')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'data' => $notification->data,
                    'is_read' => $notification->read_at !== null,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->toISOString(),
                ];
            });

        return response()->json([
            'unread_count' => $unreadCount,
            'latest' => $latestNotifications,
        ]);
    }

    /**
     * Check if there are new notifications since last check
     */
    public function checkNew(Request $request): JsonResponse
    {
        $user = $request->user();
        $lastChecked = $request->input('last_checked');

        if (!$user) {
            return response()->json([
                'has_new' => false,
                'new_count' => 0,
            ]);
        }

        $query = $user->notifications()->whereNull('read_at');

        if ($lastChecked) {
            $query->where('created_at', '>', $lastChecked);
        }

        $newCount = $query->count();

        return response()->json([
            'has_new' => $newCount > 0,
            'new_count' => $newCount,
        ]);
    }
}
