<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Services\FirebaseCloudMessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceTokenController extends Controller
{
    protected FirebaseCloudMessagingService $fcmService;

    public function __construct(FirebaseCloudMessagingService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Store a new device token for the authenticated user.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'device_type' => 'required|in:web,android,ios',
            'device_name' => 'nullable|string|max:255',
            'browser' => 'nullable|string|max:255',
            'platform' => 'nullable|string|max:255',
            'capabilities' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $deviceToken = $this->fcmService->registerDeviceToken(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Device token registered successfully',
                'data' => [
                    'id' => $deviceToken->id,
                    'device_type' => $deviceToken->device_type,
                    'device_name' => $deviceToken->device_name,
                    'is_active' => $deviceToken->is_active,
                    'created_at' => $deviceToken->created_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register device token',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get all device tokens for the authenticated user.
     */
    public function index(Request $request)
    {
        $tokens = $request->user()
            ->deviceTokens()
            ->select(['id', 'device_type', 'device_name', 'browser', 'platform', 'is_active', 'last_used_at', 'created_at'])
            ->orderBy('last_used_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tokens
        ]);
    }

    /**
     * Update a device token.
     */
    public function update(Request $request, DeviceToken $token)
    {
        // Ensure the token belongs to the authenticated user
        if ($token->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'device_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $token->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Device token updated successfully',
            'data' => $token->only(['id', 'device_type', 'device_name', 'is_active', 'last_used_at'])
        ]);
    }

    /**
     * Delete a device token.
     */
    public function destroy(Request $request, DeviceToken $token)
    {
        // Ensure the token belongs to the authenticated user
        if ($token->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found'
            ], 404);
        }

        $token->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device token deleted successfully'
        ]);
    }

    /**
     * Send a test notification to a specific device token.
     */
    public function test(Request $request, DeviceToken $token)
    {
        // Ensure the token belongs to the authenticated user
        if ($token->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found'
            ], 404);
        }

        if (!$token->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Device token is not active'
            ], 400);
        }

        try {
            $result = $this->fcmService->sendToUser($request->user(), [
                'title' => 'Test Notification',
                'body' => 'This is a test notification from PetConnect',
                'data' => [
                    'type' => 'test',
                    'timestamp' => now()->toISOString()
                ]
            ]);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Test notification sent successfully' : 'Failed to send test notification',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
