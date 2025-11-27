<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionFeature
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user can access the feature
        if (!$this->subscriptionService->canAccessFeature($user, $feature)) {
            // Determine which plan is needed
            $requiredPlan = match($feature) {
                'report_generation' => 'pro-plus',
                'analytics', 'history', 'patient_records' => 'professional',
                default => 'professional'
            };

            // If it's an API request, return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'This feature requires a subscription upgrade',
                    'feature' => $feature,
                    'required_plan' => $requiredPlan,
                    'upgrade_url' => route('subscription.index'),
                ], 403);
            }

            // Otherwise use Inertia to show upgrade page
            return \Inertia\Inertia::render('Subscription/UpgradeRequired', [
                'feature' => $feature,
                'requiredPlan' => $requiredPlan,
            ])->toResponse($request)->setStatusCode(403);
        }

        return $next($request);
    }
}
