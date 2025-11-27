<?php

namespace App\Services;

use App\Models\User;
use App\Models\SubscriptionPlan;
use Laravel\Cashier\Subscription;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    protected PayMongoService $payMongoService;

    public function __construct(PayMongoService $payMongoService)
    {
        $this->payMongoService = $payMongoService;
    }

    /**
     * Subscribe a user to a plan
     */
    public function subscribe(User $user, SubscriptionPlan $plan, string $paymentMethodId = null, string $billingCycle = 'monthly'): Subscription
    {
        try {
            DB::beginTransaction();

            // Calculate trial end date
            $trialEndsAt = $plan->trial_days > 0 
                ? Carbon::now()->addDays($plan->trial_days) 
                : null;

            // Determine price based on billing cycle
            $price = $billingCycle === 'annual' ? $plan->annual_price : $plan->price;

            // Determine initial status: if trial or free plan, start active; otherwise pending until payment
            $initialStatus = 'active';
            if ($trialEndsAt) {
                $initialStatus = 'trialing';
            }

            // Create subscription
            $subscription = $user->subscriptions()->create([
                'type' => 'default',
                'stripe_id' => 'paymongo_' . uniqid(), // Using stripe_id field for PayMongo ID
                'stripe_status' => $initialStatus,
                'stripe_price' => $plan->slug, // Store plan slug for lookup
                'quantity' => 1,
                'trial_ends_at' => $trialEndsAt,
                'ends_at' => null,
            ]);

            // Create subscription item
            $subscription->items()->create([
                'stripe_id' => 'item_' . uniqid(),
                'stripe_product' => $plan->id,
                'stripe_price' => $price,
                'quantity' => 1,
            ]);

            // If not on trial and price > 0, process payment
            if (!$trialEndsAt && $price > 0 && $paymentMethodId) {
                $this->processInitialPayment($user, $subscription, $plan, $price, $paymentMethodId);
                
                // Ensure subscription is marked as active after successful payment
                $subscription->update(['stripe_status' => 'active']);
            }

            // Update user's payment method type if provided
            if ($paymentMethodId) {
                $user->update([
                    'pm_type' => 'card',
                    'pm_last_four' => substr($paymentMethodId, -4),
                ]);
            }

            DB::commit();

            Log::info("User {$user->id} subscribed to plan {$plan->slug} with status {$subscription->stripe_status}");

            return $subscription;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Subscription creation error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process initial subscription payment
     */
    protected function processInitialPayment(User $user, Subscription $subscription, SubscriptionPlan $plan, float $amount, string $paymentMethodId): void
    {
        try {
            // Create payment intent
            $paymentIntent = $this->payMongoService->createPaymentIntent(
                (int)$amount,
                'PHP',
                [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'plan_id' => $plan->id,
                    'description' => "Subscription to {$plan->name}",
                ]
            );

            // Attach payment method
            $this->payMongoService->attachPaymentIntent(
                $paymentIntent['id'],
                $paymentMethodId,
                null,
                $price
            );

            // Store payment intent ID in subscription metadata
            $subscription->update([
                'stripe_id' => $paymentIntent['id'], // Store PayMongo payment intent ID
            ]);
        } catch (Exception $e) {
            Log::error('Initial payment processing error: ' . $e->getMessage());
            throw new Exception('Failed to process initial payment: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a subscription
     */
    public function cancel(Subscription $subscription, bool $immediately = false): Subscription
    {
        try {
            if ($immediately) {
                $subscription->update([
                    'stripe_status' => 'canceled',
                    'ends_at' => Carbon::now(),
                ]);
            } else {
                // Cancel at period end
                $endsAt = $subscription->trial_ends_at ?? Carbon::now()->addMonth();
                $subscription->update([
                    'stripe_status' => 'active', // Keep active until period end
                    'ends_at' => $endsAt,
                ]);
            }

            Log::info("Subscription {$subscription->id} canceled");

            return $subscription;
        } catch (Exception $e) {
            Log::error('Subscription cancellation error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Resume a canceled subscription
     */
    public function resume(Subscription $subscription): Subscription
    {
        try {
            if (!$subscription->ends_at || $subscription->ends_at->isPast()) {
                throw new Exception('Cannot resume expired subscription');
            }

            $subscription->update([
                'ends_at' => null,
                'stripe_status' => 'active',
            ]);

            Log::info("Subscription {$subscription->id} resumed");

            return $subscription;
        } catch (Exception $e) {
            Log::error('Subscription resume error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Swap subscription to a different plan
     */
    public function swap(Subscription $subscription, SubscriptionPlan $newPlan, string $paymentMethodId = null): Subscription
    {
        try {
            DB::beginTransaction();

            $oldPlan = SubscriptionPlan::where('slug', $subscription->stripe_price)->first();

            // Calculate proration if upgrading mid-cycle
            $prorationAmount = $this->calculateProration($subscription, $oldPlan, $newPlan);

            // Update subscription
            $subscription->update([
                'stripe_price' => $newPlan->slug,
            ]);

            // Update subscription items
            $subscription->items()->update([
                'stripe_product' => $newPlan->id,
                'stripe_price' => $newPlan->price,
            ]);

            // If there's a proration charge and payment method provided
            if ($prorationAmount > 0 && $paymentMethodId) {
                $this->processProrationPayment(
                    $subscription->user,
                    $subscription,
                    $newPlan,
                    $prorationAmount,
                    $paymentMethodId
                );
            }

            DB::commit();

            Log::info("Subscription {$subscription->id} swapped from {$oldPlan->slug} to {$newPlan->slug}");

            return $subscription;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Subscription swap error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate proration amount when changing plans
     */
    protected function calculateProration(Subscription $subscription, SubscriptionPlan $oldPlan, SubscriptionPlan $newPlan): float
    {
        // Simple proration: charge difference if upgrading
        $priceDiff = $newPlan->price - $oldPlan->price;

        if ($priceDiff <= 0) {
            return 0; // Downgrade or same price
        }

        // Calculate remaining days in current period
        $periodEnd = $subscription->trial_ends_at ?? Carbon::now()->addMonth();
        $remainingDays = Carbon::now()->diffInDays($periodEnd, false);

        if ($remainingDays <= 0) {
            return $priceDiff; // Charge full difference if at end of period
        }

        // Prorate based on remaining days (assuming 30-day month)
        return ($priceDiff / 30) * $remainingDays;
    }

    /**
     * Process proration payment
     */
    protected function processProrationPayment(User $user, Subscription $subscription, SubscriptionPlan $plan, float $amount, string $paymentMethodId): void
    {
        try {
            $paymentIntent = $this->payMongoService->createPaymentIntent(
                (int)$amount,
                'PHP',
                [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'plan_id' => $plan->id,
                    'description' => "Proration charge for plan upgrade",
                    'type' => 'proration',
                ]
            );

            $this->payMongoService->attachPaymentIntent(
                $paymentIntent['id'],
                $paymentMethodId,
                null,
                $prorationAmount
            );
        } catch (Exception $e) {
            Log::error('Proration payment error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if user has active subscription
     */
    public function hasActiveSubscription(User $user): bool
    {
        return $user->subscriptions()
            ->where('stripe_status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', Carbon::now());
            })
            ->exists();
    }

    /**
     * Get user's current plan
     */
    public function getCurrentPlan(User $user): ?SubscriptionPlan
    {
        $subscription = $user->subscriptions()
            ->whereIn('stripe_status', ['active', 'trialing'])
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', Carbon::now());
            })
            ->latest()
            ->first();

        if (!$subscription) {
            // Return free/basic plan as default for clinics
            if ($user->isClinic()) {
                return SubscriptionPlan::where('price', 0)
                    ->where('type', 'clinic')
                    ->first();
            }
            return null;
        }

        // Get plan by slug stored in stripe_price field
        $plan = SubscriptionPlan::where('slug', $subscription->stripe_price)->first();
        
        // Fallback to free plan if subscription plan not found
        if (!$plan && $user->isClinic()) {
            return SubscriptionPlan::where('price', 0)
                ->where('type', 'clinic')
                ->first();
        }
        
        return $plan;
    }

    /**
     * Check if user can access a feature
     */
    public function canAccessFeature(User $user, string $feature): bool
    {
        $plan = $this->getCurrentPlan($user);

        if (!$plan) {
            // No subscription - check default/free plan features
            $freePlan = SubscriptionPlan::where('price', 0)
                ->where('type', $user->isClinic() ? 'clinic' : 'user')
                ->first();
            
            return $freePlan ? $freePlan->hasFeature($feature) : false;
        }

        return $plan->hasFeature($feature);
    }

    /**
     * Get usage limits for user
     */
    public function getUsageLimits(User $user): array
    {
        $plan = $this->getCurrentPlan($user);

        if (!$plan) {
            $freePlan = SubscriptionPlan::where('price', 0)
                ->where('type', $user->isClinic() ? 'clinic' : 'user')
                ->first();
            
            return $freePlan ? $freePlan->limits : [];
        }

        return $plan->limits;
    }

    /**
     * Handle expired trials
     */
    public function handleExpiredTrial(Subscription $subscription): void
    {
        try {
            // If trial expired and plan is paid, attempt to charge
            $plan = SubscriptionPlan::where('slug', $subscription->stripe_price)->first();

            if ($plan && $plan->price > 0) {
                // Cancel subscription - user needs to re-subscribe with payment
                $subscription->update([
                    'stripe_status' => 'canceled',
                    'ends_at' => Carbon::now(),
                ]);

                Log::info("Trial expired for subscription {$subscription->id}, subscription canceled");
            }
        } catch (Exception $e) {
            Log::error('Expired trial handling error: ' . $e->getMessage());
        }
    }

    /**
     * Check if user can add more staff accounts
     */
    public function canAddStaff(User $user): bool
    {
        $limits = $this->getUsageLimits($user);
        $maxStaff = $limits['max_staff_accounts'] ?? 0;

        // -1 means unlimited
        if ($maxStaff === -1) {
            return true;
        }

        // Get clinic registration ID
        $clinicId = $user->clinicRegistration?->id;
        if (!$clinicId) {
            return false;
        }

        // Count current staff accounts using ClinicStaff (clinic_staff.clinic_id references clinic_registrations.id)
        $currentStaffCount = \App\Models\ClinicStaff::where('clinic_id', $clinicId)->count();

        return $currentStaffCount < $maxStaff;
    }

    /**
     * Check if user can add more services
     */
    public function canAddService(User $user): bool
    {
        $limits = $this->getUsageLimits($user);
        $maxServices = $limits['max_services'] ?? 0;

        // -1 means unlimited
        if ($maxServices === -1) {
            return true;
        }

        // Get clinic registration ID
        $clinicId = $user->clinicRegistration?->id;
        if (!$clinicId) {
            return false;
        }

        // Count current services (clinic_services.clinic_id references clinic_registrations.id)
        $currentServiceCount = \App\Models\ClinicService::where('clinic_id', $clinicId)->count();

        return $currentServiceCount < $maxServices;
    }

    /**
     * Get current counts and limits for display
     */
    public function getUsageStats(User $user): array
    {
        $limits = $this->getUsageLimits($user);
        
        $clinicRegistrationId = $user->clinicRegistration?->id;
        
        // Use clinic_registration_id since staff and services now reference clinic_registrations.id directly
        $staffCount = $clinicRegistrationId ? \App\Models\ClinicStaff::where('clinic_id', $clinicRegistrationId)->count() : 0;
        $serviceCount = $clinicRegistrationId ? \App\Models\ClinicService::where('clinic_id', $clinicRegistrationId)->count() : 0;

        return [
            'staff' => [
                'current' => $staffCount,
                'max' => $limits['max_staff_accounts'] ?? 0,
                'unlimited' => ($limits['max_staff_accounts'] ?? 0) === -1,
                'can_add' => $this->canAddStaff($user),
            ],
            'services' => [
                'current' => $serviceCount,
                'max' => $limits['max_services'] ?? 0,
                'unlimited' => ($limits['max_services'] ?? 0) === -1,
                'can_add' => $this->canAddService($user),
            ],
        ];
    }
}
