<?php

namespace App\Http\Controllers;

use App\Services\PayMongoService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected PayMongoService $payMongoService;
    protected SubscriptionService $subscriptionService;

    public function __construct(
        PayMongoService $payMongoService,
        SubscriptionService $subscriptionService
    ) {
        $this->payMongoService = $payMongoService;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Handle PayMongo webhook
     */
    public function handlePayMongo(Request $request)
    {
        // Get raw payload
        $payload = $request->getContent();
        $headers = $request->headers->all();

        // Verify webhook signature
        if (!$this->payMongoService->verifyWebhookSignature($payload, $headers)) {
            Log::warning('Invalid PayMongo webhook signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $event = $request->input('data');
        $eventType = $event['attributes']['type'] ?? null;

        Log::info('PayMongo webhook received', ['type' => $eventType]);

        try {
            switch ($eventType) {
                case 'payment.paid':
                    $this->handlePaymentPaid($event);
                    break;

                case 'payment.failed':
                    $this->handlePaymentFailed($event);
                    break;

                case 'source.chargeable':
                    $this->handleSourceChargeable($event);
                    break;

                default:
                    Log::info('Unhandled webhook event type', ['type' => $eventType]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Webhook handling error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle successful payment
     */
    protected function handlePaymentPaid($event)
    {
        $paymentData = $event['attributes']['data'];
        $metadata = $paymentData['attributes']['metadata'] ?? [];

        Log::info('Payment successful', [
            'payment_id' => $paymentData['id'],
            'amount' => $paymentData['attributes']['amount'],
            'metadata' => $metadata,
        ]);

        // Update subscription status if needed
        if (isset($metadata['subscription_id'])) {
            $subscription = \App\Models\Subscription::find($metadata['subscription_id']);
            if ($subscription) {
                $subscription->update([
                    'stripe_status' => 'active',
                    'payment_provider' => 'paymongo',
                    'payment_provider_metadata' => [
                        'payment_id' => $paymentData['id'],
                        'paid_at' => now(),
                    ],
                ]);

                Log::info("Subscription {$subscription->id} marked as active");
            }
        }

        // TODO: Send payment confirmation email
    }

    /**
     * Handle failed payment
     */
    protected function handlePaymentFailed($event)
    {
        $paymentData = $event['attributes']['data'];
        $metadata = $paymentData['attributes']['metadata'] ?? [];

        Log::warning('Payment failed', [
            'payment_id' => $paymentData['id'],
            'metadata' => $metadata,
        ]);

        // Update subscription status if needed
        if (isset($metadata['subscription_id'])) {
            $subscription = \App\Models\Subscription::find($metadata['subscription_id']);
            if ($subscription) {
                $subscription->update([
                    'stripe_status' => 'past_due',
                    'payment_provider_metadata' => [
                        'last_failed_payment' => $paymentData['id'],
                        'failed_at' => now(),
                    ],
                ]);

                Log::warning("Subscription {$subscription->id} marked as past due");
            }
        }

        // TODO: Send payment failed notification email
        // TODO: Implement retry logic
    }

    /**
     * Handle chargeable source (e-wallet ready)
     */
    protected function handleSourceChargeable($event)
    {
        $sourceData = $event['attributes']['data'];
        $metadata = $sourceData['attributes']['metadata'] ?? [];

        Log::info('Source chargeable', [
            'source_id' => $sourceData['id'],
            'amount' => $sourceData['attributes']['amount'],
        ]);

        // This is handled in the callback flow for e-wallets
        // No action needed here as we process in PaymentController@callback
    }
}
