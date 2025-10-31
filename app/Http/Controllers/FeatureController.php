<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Laravel\Pennant\Feature;

class FeatureController extends Controller
{
    /**
     * Check if the authenticated user has access to a feature.
     */
    public function check(Request $request, string $feature): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'hasFeature' => false,
                'reason' => 'User not authenticated'
            ], 401);
        }

        try {
            $hasFeature = Feature::for($user)->active($feature);
            
            return response()->json([
                'hasFeature' => $hasFeature,
                'feature' => $feature,
                'user_id' => $user->id,
                'subscription_status' => $this->getSubscriptionStatus($user)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'hasFeature' => false,
                'error' => 'Feature check failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all features for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'features' => [],
                'reason' => 'User not authenticated'
            ], 401);
        }

        $features = [
            // Pet Owner Features
            'unlimited-pets',
            'advanced-health-tracking',
            'priority-booking',
            'telemedicine',
            'health-reports',
            'export-records',
            
            // Clinic Features
            'unlimited-appointments',
            'advanced-scheduling',
            'staff-management',
            'detailed-analytics',
            'custom-forms',
            'priority-listing',
            'multi-location',
            'api-access',
            'white-label-app',
        ];

        $userFeatures = [];
        foreach ($features as $feature) {
            try {
                $userFeatures[$feature] = Feature::for($user)->active($feature);
            } catch (\Exception $e) {
                $userFeatures[$feature] = false;
            }
        }

        return response()->json([
            'features' => $userFeatures,
            'subscription_status' => $this->getSubscriptionStatus($user),
            'user_type' => $user->account_type
        ]);
    }

    /**
     * Get subscription limits for the user.
     */
    public function limits(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'limits' => [],
                'reason' => 'User not authenticated'
            ], 401);
        }

        $limits = $this->getCurrentPlanLimits($user);

        return response()->json([
            'limits' => $limits,
            'current_usage' => $this->getCurrentUsage($user),
            'subscription_status' => $this->getSubscriptionStatus($user)
        ]);
    }

    /**
     * Get subscription status for a user.
     */
    private function getSubscriptionStatus($user): array
    {
        $status = [
            'has_subscription' => false,
            'subscription_name' => 'Free',
            'subscription_type' => $user->isClinic() ? 'clinic' : 'user',
        ];

        if ($user->subscribed('default')) {
            $subscription = $user->subscription('default');
            $plan = $this->getSubscriptionPlan($subscription);
            
            $status['has_subscription'] = true;
            $status['subscription_name'] = $plan ? $plan->name : 'Unknown Plan';
            $status['stripe_price'] = $subscription->stripe_price;
            $status['current_period_end'] = $subscription->ends_at?->toISOString();
        }

        if ($user->isClinic() && $user->subscribed('clinic')) {
            $subscription = $user->subscription('clinic');
            $plan = $this->getSubscriptionPlan($subscription);
            
            $status['has_subscription'] = true;
            $status['subscription_name'] = $plan ? $plan->name : 'Unknown Plan';
            $status['stripe_price'] = $subscription->stripe_price;
            $status['current_period_end'] = $subscription->ends_at?->toISOString();
        }

        return $status;
    }

    /**
     * Get current plan limits for a user.
     */
    private function getCurrentPlanLimits($user): array
    {
        $defaultLimits = [
            'max_pets' => $user->isClinic() ? -1 : 2,
            'max_appointments_per_month' => $user->isClinic() ? 50 : 10,
            'max_staff_accounts' => $user->isClinic() ? 1 : 0,
            'max_locations' => $user->isClinic() ? 1 : 0,
            'storage_mb' => $user->isClinic() ? 500 : 100,
        ];

        if ($user->subscribed('default') || ($user->isClinic() && $user->subscribed('clinic'))) {
            $subscriptionName = $user->isClinic() ? 'clinic' : 'default';
            $subscription = $user->subscription($subscriptionName);
            $plan = $this->getSubscriptionPlan($subscription);
            
            if ($plan) {
                return array_merge($defaultLimits, $plan->limits);
            }
        }

        return $defaultLimits;
    }

    /**
     * Get current usage statistics for a user.
     */
    private function getCurrentUsage($user): array
    {
        $usage = [
            'pets_count' => 0,
            'appointments_this_month' => 0,
            'staff_accounts' => 0,
            'storage_used_mb' => 0,
        ];

        if (!$user->isClinic()) {
            // Pet owner usage
            $usage['pets_count'] = $user->pets()->count();
            $usage['appointments_this_month'] = $user->appointments()
                ->whereMonth('appointment_date', now()->month)
                ->whereYear('appointment_date', now()->year)
                ->count();
        } else {
            // Clinic usage
            $clinicRegistration = $user->clinicRegistration;
            if ($clinicRegistration) {
                $usage['appointments_this_month'] = \App\Models\Appointment::where('clinic_id', $clinicRegistration->id)
                    ->whereMonth('appointment_date', now()->month)
                    ->whereYear('appointment_date', now()->year)
                    ->count();
                
                // Count staff accounts (this would need to be implemented based on your staff management system)
                $usage['staff_accounts'] = 1; // Placeholder
                
                // Calculate storage usage (placeholder - implement based on your file storage system)
                $usage['storage_used_mb'] = 50; // Placeholder
            }
        }

        return $usage;
    }

    /**
     * Get subscription plan details from subscription.
     */
    private function getSubscriptionPlan($subscription)
    {
        if (!$subscription) {
            return null;
        }

        return \App\Models\SubscriptionPlan::where('stripe_price_id', $subscription->stripe_price)
            ->orWhere('stripe_annual_price_id', $subscription->stripe_price)
            ->first();
    }
}
