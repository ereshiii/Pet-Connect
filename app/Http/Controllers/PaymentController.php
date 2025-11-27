<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Services\PayMongoService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PaymentController extends Controller
{
    protected PayMongoService $payMongoService;
    protected SubscriptionService $subscriptionService;

    public function __construct(
        PayMongoService $payMongoService,
        SubscriptionService $subscriptionService
    ) {
        $this->middleware('auth');
        $this->payMongoService = $payMongoService;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Show checkout page
     */
    public function checkout(Request $request, string $planSlug)
    {
        $plan = SubscriptionPlan::where('slug', $planSlug)->firstOrFail();
        $user = auth()->user();

        // Check if plan type matches user type
        if ($plan->type === 'clinic' && !$user->is_clinic) {
            abort(403, 'This plan is for clinics only');
        }

        $billingCycle = $request->query('cycle', 'monthly');
        $amount = $billingCycle === 'annual' ? $plan->annual_price : $plan->price;

        // Track if coming from settings page
        $fromSettings = $request->query('from') === 'settings' || strpos($request->headers->get('referer'), '/clinic/settings') !== false;
        if ($fromSettings) {
            session(['payment_from_settings' => true]);
        }

        // Get available payment methods
        $paymentMethods = $this->payMongoService->getAvailablePaymentMethods();

        return Inertia::render('Subscription/Checkout', [
            'plan' => $plan,
            'amount' => $amount,
            'billingCycle' => $billingCycle,
            'paymentMethods' => $paymentMethods,
            'publicKey' => config('paymongo.public_key'),
        ]);
    }

    /**
     * Process subscription payment
     */
    public function processSubscription(Request $request)
    {
        $validated = $request->validate([
            'plan_slug' => 'required|exists:subscription_plans,slug',
            'billing_cycle' => 'required|in:monthly,annual',
            'payment_method' => 'required|in:card,gcash,grab_pay,paymaya',
            'payment_method_id' => 'required_if:payment_method,card',
        ]);

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $plan = SubscriptionPlan::where('slug', $validated['plan_slug'])->firstOrFail();
            $amount = $validated['billing_cycle'] === 'annual' ? $plan->annual_price : $plan->price;

            // If it's a free plan, just create subscription
            if ($amount == 0) {
                $subscription = $this->subscriptionService->subscribe(
                    $user,
                    $plan,
                    null,
                    $validated['billing_cycle']
                );

                DB::commit();

                return redirect()->route('subscription.success', ['subscription' => $subscription->id]);
            }

            // Handle payment based on method
            if ($validated['payment_method'] === 'card') {
                return $this->processCardPayment($user, $plan, $amount, $validated);
            } else {
                return $this->processEWalletPayment($user, $plan, $amount, $validated);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Subscription payment error: ' . $e->getMessage());
            
            // Return error as JSON for Inertia to handle
            return redirect()->back()->withErrors([
                'payment' => $e->getMessage()
            ]);
        }
    }

    /**
     * Process card payment
     */
    protected function processCardPayment($user, $plan, $amount, $validated)
    {
        try {
            // Clean the card number (remove spaces)
            $cleanCardNumber = str_replace(' ', '', $validated['payment_method_id']);
            
            // Format card number with spaces (standard format: XXXX XXXX XXXX XXXX)
            $formattedCardNumber = trim(chunk_split($cleanCardNumber, 4, ' '));
            
            // Check if this is a mock payment card (match by card_number with or without spaces, or card_id)
            $mockCard = DB::table('mock_payment_cards')
                ->where(function($query) use ($cleanCardNumber, $formattedCardNumber, $validated) {
                    // Match by card number without spaces
                    $query->where('card_number', $cleanCardNumber)
                          // OR match by card number with spaces
                          ->orWhere('card_number', $formattedCardNumber)
                          // OR match by card_id
                          ->orWhere('card_id', $validated['payment_method_id'])
                          // OR match by card_id cleaned
                          ->orWhere('card_id', $cleanCardNumber);
                })
                ->first();

            if ($mockCard) {
                // Only validate sufficient balance
                if ($mockCard->balance < $amount) {
                    DB::rollBack();
                    return redirect()->route('subscription.failed', [
                        'error' => 'Insufficient card balance. Available: ₱' . number_format($mockCard->balance, 2),
                        'planName' => $plan->name,
                        'amount' => $amount,
                    ]);
                }

                // Cancel any existing active subscriptions for this user
                DB::table('subscriptions')
                    ->where('user_id', $user->id)
                    ->where('type', 'default')
                    ->whereNull('ends_at')
                    ->update([
                        'ends_at' => now(),
                        'updated_at' => now(),
                    ]);

                // Deduct balance from mock card
                DB::table('mock_payment_cards')
                    ->where('card_id', $mockCard->card_id)
                    ->decrement('balance', $amount);

                // Create mock subscription
                $subscription = DB::table('subscriptions')->insertGetId([
                    'user_id' => $user->id,
                    'type' => 'default',
                    'stripe_id' => 'mock_sub_' . uniqid(),
                    'stripe_status' => 'active',
                    'stripe_price' => $plan->slug,  // Use plan slug to identify the subscription plan
                    'quantity' => 1,
                    'trial_ends_at' => null,
                    'ends_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::commit();

                return redirect()->route('subscription.success', ['subscription' => $subscription]);
            }

            // If no mock card found, this is considered an invalid card
            DB::rollBack();
            return redirect()->route('subscription.failed', [
                'error' => 'Card not found. Please use a card registered in the Mock Payment page.',
                'planName' => $plan->name,
                'amount' => $amount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('subscription.failed', [
                'error' => $e->getMessage(),
                'planName' => $plan->name ?? null,
                'amount' => $amount ?? null,
            ]);
        }
    }

    /**
     * Process e-wallet payment (GCash, GrabPay, Maya)
     */
    protected function processEWalletPayment($user, $plan, $amount, $validated)
    {
        try {
            // Create source for e-wallet
            $source = $this->payMongoService->createSource(
                $validated['payment_method'],
                $amount,
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '+639171234567',
                ],
                route('payment.callback'),
                [
                    'user_id' => $user->id,
                    'plan_slug' => $plan->slug,
                    'billing_cycle' => $validated['billing_cycle'],
                ]
            );

            // Store source ID in session for callback processing
            session([
                'payment_source_id' => $source['id'],
                'payment_plan_slug' => $plan->slug,
                'payment_billing_cycle' => $validated['billing_cycle'],
            ]);

            DB::commit();

            // Redirect to payment provider (GCash/GrabPay/Maya)
            return redirect($source['attributes']['redirect']['checkout_url']);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Handle payment callback from PayMongo
     */
    public function callback(Request $request)
    {
        $status = $request->query('status');
        // Get source ID from query parameter first, then fall back to session
        $sourceId = $request->query('source') ?? session('payment_source_id');

        if (!$sourceId) {
            return redirect()->route('subscription.index')->with('error', 'Invalid payment session');
        }

        try {
            // Retrieve source to check status
            $source = $this->payMongoService->retrieveSource($sourceId);
            
            if ($source['attributes']['status'] === 'chargeable') {
                // Create payment
                $payment = $this->payMongoService->createPayment(
                    $sourceId,
                    $source['attributes']['amount'] / 100,
                    'PHP',
                    $source['attributes']['metadata']
                );

                // Create subscription
                $user = auth()->user();
                $planSlug = session('payment_plan_slug');
                $billingCycle = session('payment_billing_cycle');
                $plan = SubscriptionPlan::where('slug', $planSlug)->firstOrFail();

                $subscription = $this->subscriptionService->subscribe(
                    $user,
                    $plan,
                    $payment['id'],
                    $billingCycle
                );

                // Clear session
                session()->forget(['payment_source_id', 'payment_plan_slug', 'payment_billing_cycle']);

                return redirect()->route('subscription.success', ['subscription' => $subscription->id]);
            }

            throw new \Exception('Payment not completed');

        } catch (\Exception $e) {
            Log::error('Payment callback error: ' . $e->getMessage());
            return redirect()->route('subscription.failed')->with('error', $e->getMessage());
        }
    }

    /**
     * Show success page
     */
    public function success(Request $request)
    {
        $subscription = auth()->user()->subscriptions()->findOrFail($request->subscription);
        $plan = $this->subscriptionService->getCurrentPlan(auth()->user());

        // Check if should redirect to settings
        $redirectToSettings = $request->query('redirect') === 'settings' || session('payment_from_settings');
        session()->forget('payment_from_settings');

        // Determine billing cycle - we'll default to monthly for now since we don't track it separately
        $billingCycle = 'monthly'; // TODO: Store billing cycle in subscription or determine from price

        return Inertia::render('Subscription/Success', [
            'planName' => $plan->name ?? 'Unknown Plan',
            'amount' => $plan->price ?? 0,
            'billingCycle' => $billingCycle,
            'transactionId' => $subscription->stripe_id,
            'redirectTo' => $redirectToSettings ? route('clinic.settings.subscription.edit') : route('subscription.dashboard'),
        ]);
    }

    /**
     * Show failed payment page
     */
    public function failed(Request $request)
    {
        return Inertia::render('Subscription/Failed', [
            'error' => $request->session()->get('error'),
            'planName' => $request->session()->get('payment_plan_name'),
            'amount' => $request->session()->get('payment_amount'),
        ]);
    }

    /**
     * Get available payment methods with fees
     */
    public function paymentMethods()
    {
        return response()->json([
            'methods' => $this->payMongoService->getAvailablePaymentMethods(),
        ]);
    }

    /**
     * Calculate payment total with fees
     */
    public function calculateTotal(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:card,gcash,grab_pay,paymaya',
        ]);

        try {
            $result = $this->payMongoService->calculateTotalWithFees(
                $validated['amount'],
                $validated['payment_method']
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
