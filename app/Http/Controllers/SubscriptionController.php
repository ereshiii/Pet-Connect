<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->middleware('auth');
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display subscription plans
     */
    public function index()
    {
        $user = auth()->user();
        $currentPlan = $this->subscriptionService->getCurrentPlan($user);
        $currentSubscription = $user->subscription('default');

        // Get all clinic plans (only clinics can subscribe, pet owners have no subscriptions)
        $plans = SubscriptionPlan::active()
            ->where('type', 'clinic')
            ->orderBy('price')
            ->get();

        // Get saved payment method
        $savedPaymentMethod = null;
        if ($user->pm_type && $user->pm_last_four) {
            $savedPaymentMethod = [
                'type' => $user->pm_type,
                'last_four' => $user->pm_last_four,
            ];
        }

        return Inertia::render('Subscription/Index', [
            'plans' => $plans,
            'currentPlan' => $currentPlan,
            'hasActiveSubscription' => $currentSubscription && $currentSubscription->active(),
            'savedPaymentMethod' => $savedPaymentMethod,
        ]);
    }

    /**
     * Show subscription dashboard
     */
    public function dashboard()
    {
        $user = auth()->user();
        $currentPlan = $this->subscriptionService->getCurrentPlan($user);
        $currentSubscription = $user->subscription('default');
        $usageLimits = $this->subscriptionService->getUsageLimits($user);

        // Get usage statistics for clinics
        $stats = [
            'appointments_this_month' => 0,
            'appointments_limit' => $usageLimits['max_appointments_per_month'] ?? 'unlimited',
            'staff_count' => 0,
            'staff_limit' => $usageLimits['max_staff_accounts'] ?? 'unlimited',
            'transaction_fee' => $usageLimits['transaction_fee_percentage'] ?? 0,
        ];
        
        if ($user->is_clinic && $user->clinic) {
            $stats['appointments_this_month'] = $user->clinic->appointments()
                ->whereMonth('scheduled_at', now()->month)
                ->count();
            $stats['staff_count'] = $user->clinic->staff()->count();
        }

        return Inertia::render('Subscription/Dashboard', [
            'currentPlan' => $currentPlan,
            'subscription' => $currentSubscription,
            'stats' => $stats,
            'usageLimits' => $usageLimits,
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        $user = auth()->user();
        $subscription = $user->subscription('default');

        if (!$subscription || !$subscription->active()) {
            return back()->with('error', 'No active subscription to cancel');
        }

        $immediately = $request->boolean('immediately', false);
        
        $this->subscriptionService->cancel($subscription, $immediately);

        // Check if request came from settings page
        $referer = $request->headers->get('referer');
        $redirectRoute = (strpos($referer, '/clinic/settings/subscription') !== false)
            ? 'clinic.settings.subscription.edit'
            : 'subscription.dashboard';

        return redirect()->route($redirectRoute)->with(
            'success',
            $immediately 
                ? 'Subscription canceled immediately' 
                : 'Subscription will be canceled at the end of the billing period'
        );
    }

    /**
     * Resume canceled subscription
     */
    public function resume()
    {
        $user = auth()->user();
        $subscription = $user->subscription('default');

        if (!$subscription || !$subscription->ends_at) {
            return back()->with('error', 'No subscription to resume');
        }

        try {
            $this->subscriptionService->resume($subscription);
            
            // Check if request came from settings page
            $referer = request()->headers->get('referer');
            $redirectRoute = (strpos($referer, '/clinic/settings/subscription') !== false)
                ? 'clinic.settings.subscription.edit'
                : 'subscription.dashboard';
            
            return redirect()->route($redirectRoute)->with('success', 'Subscription resumed successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show upgrade options
     */
    public function upgrade()
    {
        $user = auth()->user();
        $currentPlan = $this->subscriptionService->getCurrentPlan($user);

        $plans = SubscriptionPlan::active()
            ->where('type', $user->is_clinic ? 'clinic' : 'user')
            ->where('price', '>', $currentPlan ? $currentPlan->price : 0)
            ->orderBy('price')
            ->get();

        return Inertia::render('Subscription/Upgrade', [
            'currentPlan' => $currentPlan,
            'plans' => $plans,
        ]);
    }
}
