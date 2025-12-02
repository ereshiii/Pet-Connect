<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceTokenController extends Controller
{
    /**
     * Store or update a device token
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'device_type' => 'sometimes|string|in:web,android,ios',
            'device_name' => 'nullable|string|max:255',
            'browser' => 'nullable|string|max:100',
            'platform' => 'nullable|string|max:100',
            'capabilities' => 'nullable|array',
        ]);

        $user = Auth::user();

        // Check if token already exists
        $deviceToken = DeviceToken::where('token', $validated['token'])->first();

        if ($deviceToken) {
            // Update existing token
            $deviceToken->update([
                'user_id' => $user->id,
                'device_type' => $validated['device_type'] ?? 'web',
                'device_name' => $validated['device_name'] ?? null,
                'browser' => $validated['browser'] ?? null,
                'platform' => $validated['platform'] ?? null,
                'capabilities' => $validated['capabilities'] ?? null,
                'is_active' => true,
                'last_used_at' => now(),
            ]);
        } else {
            // Create new token
            $deviceToken = DeviceToken::create([
                'user_id' => $user->id,
                'token' => $validated['token'],
                'device_type' => $validated['device_type'] ?? 'web',
                'device_name' => $validated['device_name'] ?? null,
                'browser' => $validated['browser'] ?? null,
                'platform' => $validated['platform'] ?? null,
                'capabilities' => $validated['capabilities'] ?? null,
                'is_active' => true,
                'last_used_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Device token saved successfully',
            'device' => $deviceToken->display_name,
        ]);
    }

    /**
     * Get all user's device tokens
     */
    public function index()
    {
        $user = Auth::user();
        $tokens = $user->deviceTokens()->latest()->get();

        return response()->json([
            'tokens' => $tokens,
        ]);
    }

    /**
     * Delete a specific device token
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $deviceToken = DeviceToken::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $deviceToken->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device token removed successfully',
        ]);
    }

    /**
     * Update notification preferences for a device
     */
    public function updatePreferences(Request $request, $id)
    {
        $validated = $request->validate([
            'channel' => 'required|string',
            'enabled' => 'required|boolean',
        ]);

        $user = Auth::user();
        $deviceToken = DeviceToken::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $deviceToken->setNotificationPreference($validated['channel'], $validated['enabled']);

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated',
        ]);
    }
}
