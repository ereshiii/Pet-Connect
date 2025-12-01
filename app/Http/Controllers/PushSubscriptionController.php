<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PushSubscription;

class PushSubscriptionController extends Controller
{
    /**
     * Store a new push subscription.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys' => 'required|array',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $user = Auth::user();

        // Delete existing subscription for this endpoint if any
        PushSubscription::where('endpoint', $validated['endpoint'])->delete();

        // Create new subscription
        $subscription = PushSubscription::create([
            'user_id' => $user->id,
            'endpoint' => $validated['endpoint'],
            'public_key' => $validated['keys']['p256dh'],
            'auth_token' => $validated['keys']['auth'],
        ]);

        return response()->json([
            'message' => 'Push subscription saved successfully',
            'subscription' => $subscription,
        ]);
    }

    /**
     * Delete the user's push subscription.
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        
        PushSubscription::where('user_id', $user->id)->delete();

        return response()->json([
            'message' => 'Push subscription deleted successfully',
        ]);
    }

    /**
     * Get the user's current subscription status.
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        
        $subscription = PushSubscription::where('user_id', $user->id)->first();

        return response()->json([
            'subscribed' => $subscription !== null,
            'subscription' => $subscription,
        ]);
    }
}
