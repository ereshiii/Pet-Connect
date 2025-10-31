<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;
use App\Models\User;

class FeatureServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Pet Owner Features
        Feature::define('unlimited-pets', function (User $user) {
            // Free users can have up to 2 pets, premium users unlimited
            if (!$user->subscribed('default')) {
                return false; // Free tier limited to 2 pets
            }
            
            $subscription = $user->subscription('default');
            $plan = $this->getSubscriptionPlan($subscription);
            
            return $plan && $plan->hasFeature('unlimited_pets');
        });

        Feature::define('advanced-health-tracking', function (User $user) {
            if (!$user->subscribed('default')) {
                return false;
            }
            
            $subscription = $user->subscription('default');
            $plan = $this->getSubscriptionPlan($subscription);
            
            return $plan && $plan->hasFeature('advanced_health_tracking');
        });

        Feature::define('priority-booking', function (User $user) {
            if (!$user->subscribed('default')) {
                return false;
            }
            
            $subscription = $user->subscription('default');
            $plan = $this->getSubscriptionPlan($subscription);
            
            return $plan && $plan->hasFeature('priority_booking');
        });

        Feature::define('telemedicine', function (User $user) {
            if (!$user->subscribed('default')) {
                return false;
            }
            
            $subscription = $user->subscription('default');
            $plan = $this->getSubscriptionPlan($subscription);
            
            return $plan && $plan->hasFeature('telemedicine');
        });

        Feature::define('health-reports', function (User $user) {
            if (!$user->subscribed('default')) {
                return false;
            }
            
            $subscription = $user->subscription('default');
            $plan = $this->getSubscriptionPlan($subscription);
            
            return $plan && $plan->hasFeature('health_reports');
        });

        Feature::define('export-records', function (User $user) {
            if (!$user->subscribed('default')) {
                return false;
            }
            
            $subscription = $user->subscription('default');
            $plan = $this->getSubscriptionPlan($subscription);
            
            return $plan && $plan->hasFeature('export_records');
        });

        // Clinic Features
        Feature::define('unlimited-appointments', function (User $user) {
            if (!$user->isClinic()) {
                return false;
            }

            if (!$user->subscribed('clinic')) {
                return false; // Free clinic tier limited to 50 appointments/month
            }
            
            $subscription = $user->subscription('clinic');
            $plan = $this->getSubscriptionPlan($subscription);
            
            return $plan && $plan->hasFeature('unlimited_appointments');
        });

        Feature::define('advanced-scheduling', function (User $user) {
            if (!$user->isClinic() || !$user->subscribed('clinic')) {
                return false;
            }
            
            $subscription = $user->subscription('clinic');
            $plan = $this->getSubscriptionPlan($subscription);
            
            return $plan && $plan->hasFeature('advanced_scheduling');
        });

        Feature::define('staff-management', function (User $user) {
            if (!$user->isClinic() || !$user->subscribed('clinic')) {
                return false;
            }
            
            $subscription = $user->subscription('clinic');
            $plan = $this->getSubscriptionPlan($subscription);
            
            return $plan && $plan->hasFeature('staff_management');
        });

        Feature::define('detailed-analytics', function (User $user) {
            if (!$user->isClinic() || !$user->subscribed('clinic')) {
                return false;
            }
            
            $subscription = $user->subscription('clinic');
            $plan = $this->getSubscriptionPlan($subscription);
            
            return $plan && $plan->hasFeature('detailed_analytics');
        });

        Feature::define('custom-forms', function (User $user) {
            if (!$user->isClinic() || !$user->subscribed('clinic')) {
                return false;
            }
            
            $subscription = $user->subscription('clinic');
            $plan = $this->getSubscriptionPlan($subscription);
            
            return $plan && $plan->hasFeature('custom_forms');
        });

        Feature::define('priority-listing', function (User $user) {
            if (!$user->isClinic() || !$user->subscribed('clinic')) {
                return false;
            }
            
            $subscription = $user->subscription('clinic');
            $plan = $this->getSubscriptionPlan($subscription);
            
            return $plan && $plan->hasFeature('priority_listing');
        });

        Feature::define('multi-location', function (User $user) {
            if (!$user->isClinic() || !$user->subscribed('clinic')) {
                return false;
            }
            
            $subscription = $user->subscription('clinic');
            $plan = $this->getSubscriptionPlan($subscription);
            
            return $plan && $plan->hasFeature('multi_location');
        });

        Feature::define('api-access', function (User $user) {
            if (!$user->isClinic() || !$user->subscribed('clinic')) {
                return false;
            }
            
            $subscription = $user->subscription('clinic');
            $plan = $this->getSubscriptionPlan($subscription);
            
            return $plan && $plan->hasFeature('api_access');
        });

        Feature::define('white-label-app', function (User $user) {
            if (!$user->isClinic() || !$user->subscribed('clinic')) {
                return false;
            }
            
            $subscription = $user->subscription('clinic');
            $plan = $this->getSubscriptionPlan($subscription);
            
            return $plan && $plan->hasFeature('white_label_app');
        });
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