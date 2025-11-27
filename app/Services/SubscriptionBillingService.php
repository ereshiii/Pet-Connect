<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SubscriptionBillingService
{
    /**
     * Generate billing history for a subscription
     */
    public function generateBillingHistory(int $subscriptionId): int
    {
        $subscription = DB::table('subscriptions')->find($subscriptionId);
        
        if (!$subscription) {
            return 0;
        }

        // Get plan price
        $plan = DB::table('subscription_plans')
            ->where('slug', $subscription->stripe_price)
            ->first();

        if (!$plan) {
            return 0;
        }

        $price = (float) $plan->price;
        $startDate = Carbon::parse($subscription->created_at);
        $endDate = $subscription->ends_at ? Carbon::parse($subscription->ends_at) : now();
        
        // Get random mock payment card for this user
        $mockCard = DB::table('mock_payment_cards')
            ->inRandomOrder()
            ->first();

        $recordsCreated = 0;
        $currentBillingDate = $startDate->copy();

        // Generate billing records for each month
        while ($currentBillingDate->lessThanOrEqualTo($endDate)) {
            $periodStart = $currentBillingDate->copy();
            $periodEnd = $currentBillingDate->copy()->addMonth()->subDay();
            
            // Check if billing record already exists
            $exists = DB::table('subscription_billing_history')
                ->where('subscription_id', $subscriptionId)
                ->where('billing_date', $currentBillingDate->format('Y-m-d'))
                ->exists();

            if (!$exists) {
                DB::beginTransaction();
                
                try {
                    DB::table('subscription_billing_history')->insert([
                        'subscription_id' => $subscriptionId,
                        'user_id' => $subscription->user_id,
                        'amount' => $price,
                        'billing_period' => 'monthly',
                        'billing_date' => $currentBillingDate->format('Y-m-d H:i:s'),
                        'period_start' => $periodStart->format('Y-m-d'),
                        'period_end' => $periodEnd->format('Y-m-d'),
                        'status' => 'paid',
                        'payment_method' => 'mock_card',
                        'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                        'mock_card_id' => $mockCard ? $mockCard->id : null,
                        'notes' => 'Auto-generated billing record',
                        'created_at' => $currentBillingDate->format('Y-m-d H:i:s'),
                        'updated_at' => now(),
                    ]);

                    // Deduct from mock card balance
                    if ($mockCard) {
                        DB::table('mock_payment_cards')
                            ->where('id', $mockCard->id)
                            ->decrement('balance', $price);
                    }
                    
                    // Add to merchant account
                    $this->creditMerchantAccount($price);

                    DB::commit();
                    $recordsCreated++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Failed to create billing record: {$e->getMessage()}");
                }
            }

            $currentBillingDate->addMonth();
        }

        return $recordsCreated;
    }

    /**
     * Process billing for a specific subscription (for automated renewal)
     */
    public function processBilling(int $subscriptionId): bool
    {
        $subscription = DB::table('subscriptions')->find($subscriptionId);
        
        if (!$subscription) {
            Log::error("Subscription not found: {$subscriptionId}");
            return false;
        }

        // Check if subscription is active
        if ($subscription->ends_at && Carbon::parse($subscription->ends_at)->isPast()) {
            Log::info("Subscription {$subscriptionId} has ended");
            return false;
        }

        // Get plan price
        $plan = DB::table('subscription_plans')
            ->where('slug', $subscription->stripe_price)
            ->first();

        if (!$plan) {
            Log::error("Plan not found for subscription: {$subscriptionId}");
            return false;
        }

        $price = (float) $plan->price;
        
        // Get mock payment card for this user
        $mockCard = DB::table('mock_payment_cards')
            ->where('balance', '>=', $price)
            ->inRandomOrder()
            ->first();

        if (!$mockCard) {
            Log::error("No mock card with sufficient balance for subscription: {$subscriptionId}");
            return false;
        }

        // Calculate billing period
        $billingDate = now();
        $periodStart = $billingDate->copy();
        $periodEnd = $billingDate->copy()->addMonth()->subDay();

        try {
            DB::beginTransaction();

            // Create billing record
            DB::table('subscription_billing_history')->insert([
                'subscription_id' => $subscriptionId,
                'user_id' => $subscription->user_id,
                'amount' => $price,
                'billing_period' => 'monthly',
                'billing_date' => $billingDate->format('Y-m-d H:i:s'),
                'period_start' => $periodStart->format('Y-m-d'),
                'period_end' => $periodEnd->format('Y-m-d'),
                'status' => 'paid',
                'payment_method' => 'mock_card',
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                'mock_card_id' => $mockCard->id,
                'notes' => 'Automated monthly billing',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Deduct from mock card balance
            DB::table('mock_payment_cards')
                ->where('id', $mockCard->id)
                ->decrement('balance', $price);
            
            // Add to merchant account
            $this->creditMerchantAccount($price);

            DB::commit();
            
            Log::info("Successfully processed billing for subscription: {$subscriptionId}");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process billing for subscription {$subscriptionId}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Get total revenue from billing history
     */
    public function getTotalRevenue(): float
    {
        return (float) DB::table('subscription_billing_history')
            ->where('status', 'paid')
            ->sum('amount');
    }

    /**
     * Get revenue for a specific period
     */
    public function getRevenueForPeriod(Carbon $startDate, Carbon $endDate): float
    {
        return (float) DB::table('subscription_billing_history')
            ->where('status', 'paid')
            ->whereBetween('billing_date', [$startDate, $endDate])
            ->sum('amount');
    }

    /**
     * Get subscription revenue by plan
     */
    public function getRevenueByPlan(): array
    {
        $revenue = DB::table('subscription_billing_history')
            ->join('subscriptions', 'subscription_billing_history.subscription_id', '=', 'subscriptions.id')
            ->join('subscription_plans', 'subscriptions.stripe_price', '=', 'subscription_plans.slug')
            ->where('subscription_billing_history.status', 'paid')
            ->select('subscription_plans.slug', 'subscription_plans.name', DB::raw('SUM(subscription_billing_history.amount) as total'))
            ->groupBy('subscription_plans.slug', 'subscription_plans.name')
            ->get();

        return $revenue->mapWithKeys(function ($item) {
            return [$item->slug => [
                'name' => $item->name,
                'total' => (float) $item->total
            ]];
        })->toArray();
    }
    
    /**
     * Credit the merchant account with revenue
     */
    private function creditMerchantAccount(float $amount): void
    {
        // Get or create merchant account
        $merchantAccount = DB::table('mock_payment_cards')
            ->where('card_id', 'MERCH-PETCONNECT-001')
            ->first();
        
        if ($merchantAccount) {
            // Increment merchant account balance
            DB::table('mock_payment_cards')
                ->where('card_id', 'MERCH-PETCONNECT-001')
                ->increment('balance', $amount);
        }
    }
}
