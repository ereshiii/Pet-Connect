<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Mock PayMongo Service
 * 
 * Simulates PayMongo API responses without requiring actual PayMongo account.
 * Perfect for development, testing, and demonstrations.
 * 
 * TEST CARDS:
 * ✅ Success Cards (registered with sufficient balance):
 *    - 4343434343434345 (Default Visa - Balance: ₱50,000)
 *    - 4571736000000075 (Visa - Balance: ₱10,000)
 *    - 5455590000000009 (Mastercard - Balance: ₱25,000)
 * 
 * ⚠️ Low Balance Cards:
 *    - 4532015112830366 (Visa - Balance: ₱500 - will fail for amounts > ₱500)
 *    - 5425233430109903 (Mastercard - Balance: ₱100 - will fail for most payments)
 * 
 * ❌ Fail Cards (registered to fail):
 *    - 4571000000000000 (Generic decline)
 *    - 4000000000000002 (Card declined)
 * 
 * ⚠️ Any other 16-digit card number will be rejected as "unregistered test card"
 * 
 * E-WALLET: All e-wallet payments (GCash, GrabPay, Maya) succeed when authorized
 * 
 * CARD BALANCES: Balances are tracked in session and auto-deduct on successful payments
 */
class MockPayMongoService
{
    /**
     * Get mock card balance (stored in session)
     */
    private function getCardBalance(string $cardNumber): float
    {
        $balances = session('mock_card_balances', [
            '4343434343434345' => 50000.00,
            '4571736000000075' => 10000.00,
            '5455590000000009' => 25000.00,
            '4532015112830366' => 500.00,
            '5425233430109903' => 100.00,
        ]);

        return $balances[$cardNumber] ?? 0;
    }

    /**
     * Deduct amount from card balance
     */
    private function deductCardBalance(string $cardNumber, float $amount): void
    {
        $balances = session('mock_card_balances', [
            '4343434343434345' => 50000.00,
            '4571736000000075' => 10000.00,
            '5455590000000009' => 25000.00,
            '4532015112830366' => 500.00,
            '5425233430109903' => 100.00,
        ]);

        if (isset($balances[$cardNumber])) {
            $balances[$cardNumber] -= $amount;
            session(['mock_card_balances' => $balances]);
        }
    }
    /**
     * Create a mock payment intent (for card payments)
     */
    public function createPaymentIntent(float $amount, array $metadata = []): array
    {
        $intentId = 'pi_mock_' . Str::random(24);

        return [
            'id' => $intentId,
            'type' => 'payment_intent',
            'attributes' => [
                'amount' => $amount * 100, // Convert to centavos
                'currency' => 'PHP',
                'status' => 'awaiting_payment_method',
                'description' => $metadata['description'] ?? 'Subscription payment',
                'statement_descriptor' => 'PetConnect',
                'metadata' => $metadata,
                'created_at' => now()->timestamp,
                'updated_at' => now()->timestamp,
            ],
        ];
    }

    /**
     * Attach payment method to intent (simulate card payment)
     */
    public function attachPaymentIntent(string $intentId, string $paymentMethodId, float $amount = 0): array
    {
        // Registered test cards that will succeed (with balances)
        $successCards = [
            '4343434343434345', // Balance: ₱50,000
            '4571736000000075', // Balance: ₱10,000
            '5455590000000009', // Balance: ₱25,000
            '4532015112830366', // Balance: ₱500 (low balance)
            '5425233430109903', // Balance: ₱100 (very low balance)
        ];

        // Test cards that will fail
        $failCards = [
            '4571000000000000', // Generic fail card
            '4000000000000002', // Card declined
        ];

        // Determine status based on card number
        $status = 'succeeded';
        $lastError = null;

        // Extract card number from payment method ID (format: pm_card_XXXXXXXXXXXX)
        $cardNumber = str_replace('pm_card_', '', $paymentMethodId);

        // Check if card is in fail list
        if (in_array($cardNumber, $failCards) || str_contains($paymentMethodId, 'fail')) {
            $status = 'failed';
            $lastError = [
                'code' => 'card_declined',
                'message' => 'Your card was declined. Please use a different card or payment method.',
            ];
        } 
        // If not in success list and not explicitly failing, treat as unregistered
        elseif (!in_array($cardNumber, $successCards) && strlen($cardNumber) === 16) {
            $status = 'failed';
            $lastError = [
                'code' => 'test_card_not_registered',
                'message' => 'This test card is not registered. Please use a registered test card.',
            ];
        }
        // Check balance if it's a success card
        elseif (in_array($cardNumber, $successCards) && $amount > 0) {
            $balance = $this->getCardBalance($cardNumber);
            
            if ($balance < $amount) {
                $status = 'failed';
                $lastError = [
                    'code' => 'insufficient_funds',
                    'message' => sprintf(
                        'Insufficient funds. Card balance: ₱%s, Required: ₱%s',
                        number_format($balance, 2),
                        number_format($amount, 2)
                    ),
                ];
            } else {
                // Deduct amount from balance on success
                $this->deductCardBalance($cardNumber, $amount);
            }
        }

        return [
            'id' => $intentId,
            'type' => 'payment_intent',
            'attributes' => [
                'amount' => $amount * 100, // Convert to centavos
                'currency' => 'PHP',
                'status' => $status,
                'last_payment_error' => $lastError,
                'payments' => $status === 'succeeded' ? [
                    [
                        'id' => 'pay_mock_' . Str::random(24),
                        'type' => 'payment',
                        'attributes' => [
                            'amount' => $amount * 100,
                            'currency' => 'PHP',
                            'status' => 'paid',
                            'paid_at' => now()->timestamp,
                        ],
                    ],
                ] : [],
                'payment_method_allowed' => ['card'],
                'created_at' => now()->timestamp,
                'updated_at' => now()->timestamp,
            ],
        ];
    }

    /**
     * Create a source (for e-wallet payments)
     */
    public function createSource(
        string $type,
        float $amount,
        array $billing,
        string $redirectUrl,
        array $metadata = []
    ): array {
        $sourceId = 'src_mock_' . Str::random(24);
        
        // Generate mock checkout URL
        $checkoutUrl = url('/mock-payment/authorize?source=' . $sourceId . '&type=' . $type);

        return [
            'id' => $sourceId,
            'type' => 'source',
            'attributes' => [
                'amount' => $amount * 100,
                'currency' => 'PHP',
                'type' => $type,
                'status' => 'pending',
                'redirect' => [
                    'checkout_url' => $checkoutUrl,
                    'success' => $redirectUrl . '?status=success',
                    'failed' => $redirectUrl . '?status=failed',
                ],
                'billing' => $billing,
                'metadata' => $metadata,
                'created_at' => now()->timestamp,
                'updated_at' => now()->timestamp,
            ],
        ];
    }

    /**
     * Retrieve source status
     */
    public function retrieveSource(string $sourceId): array
    {
        // Simulate that source is chargeable (user completed payment)
        return [
            'id' => $sourceId,
            'type' => 'source',
            'attributes' => [
                'amount' => 149900,
                'currency' => 'PHP',
                'type' => 'gcash',
                'status' => 'chargeable', // Ready to be charged
                'metadata' => [],
                'created_at' => now()->timestamp,
                'updated_at' => now()->timestamp,
            ],
        ];
    }

    /**
     * Create payment from source
     */
    public function createPayment(
        string $sourceId,
        float $amount,
        string $currency,
        array $metadata = []
    ): array {
        $paymentId = 'pay_mock_' . Str::random(24);

        return [
            'id' => $paymentId,
            'type' => 'payment',
            'attributes' => [
                'amount' => $amount * 100,
                'currency' => $currency,
                'status' => 'paid',
                'source' => [
                    'id' => $sourceId,
                    'type' => 'source',
                ],
                'metadata' => $metadata,
                'paid_at' => now()->timestamp,
                'created_at' => now()->timestamp,
            ],
        ];
    }

    /**
     * Verify webhook signature (always return true for mock)
     */
    public function verifyWebhookSignature(array $payload, array $headers): bool
    {
        // In mock mode, always accept webhooks
        return true;
    }

    /**
     * Get available payment methods
     */
    public function getAvailablePaymentMethods(): array
    {
        return [
            [
                'type' => 'card',
                'name' => 'Credit/Debit Card',
                'description' => 'Visa, Mastercard, etc.',
                'fee_percentage' => 3.5,
                'fee_fixed' => 15.00,
            ],
            [
                'type' => 'gcash',
                'name' => 'GCash',
                'description' => 'GCash e-wallet',
                'fee_percentage' => 2.5,
                'fee_fixed' => 15.00,
            ],
            [
                'type' => 'grab_pay',
                'name' => 'GrabPay',
                'description' => 'GrabPay e-wallet',
                'fee_percentage' => 2.5,
                'fee_fixed' => 15.00,
            ],
            [
                'type' => 'paymaya',
                'name' => 'PayMaya',
                'description' => 'PayMaya e-wallet',
                'fee_percentage' => 3.5,
                'fee_fixed' => 15.00,
            ],
        ];
    }

    /**
     * Calculate total with fees
     */
    public function calculateTotalWithFees(float $amount, string $paymentMethod): array
    {
        $feeRates = [
            'card' => ['percentage' => 3.5, 'fixed' => 15],
            'gcash' => ['percentage' => 2.5, 'fixed' => 15],
            'grab_pay' => ['percentage' => 2.5, 'fixed' => 15],
            'paymaya' => ['percentage' => 3.5, 'fixed' => 15],
        ];

        $rate = $feeRates[$paymentMethod] ?? $feeRates['card'];
        
        $percentageFee = $amount * ($rate['percentage'] / 100);
        $fixedFee = $rate['fixed'];
        $totalFee = $percentageFee + $fixedFee;
        $total = $amount + $totalFee;

        return [
            'amount' => $amount,
            'fee' => round($totalFee, 2),
            'total' => round($total, 2),
            'currency' => 'PHP',
        ];
    }

    /**
     * Create mock payment method (for card)
     */
    public function createPaymentMethod(array $cardDetails): array
    {
        $methodId = 'pm_mock_' . Str::random(24);
        
        // Determine card type from number
        $cardNumber = $cardDetails['card_number'] ?? '';
        $cardType = 'visa';
        
        if (str_starts_with($cardNumber, '5')) {
            $cardType = 'mastercard';
        }

        return [
            'id' => $methodId,
            'type' => 'payment_method',
            'attributes' => [
                'type' => 'card',
                'billing' => $cardDetails['billing'] ?? [],
                'details' => [
                    'last4' => substr($cardNumber, -4),
                    'exp_month' => $cardDetails['exp_month'] ?? 12,
                    'exp_year' => $cardDetails['exp_year'] ?? 2025,
                    'card_type' => $cardType,
                ],
                'created_at' => now()->timestamp,
            ],
        ];
    }

    /**
     * Simulate webhook event
     */
    public function simulateWebhookEvent(string $eventType, array $data): array
    {
        return [
            'id' => 'evt_mock_' . Str::random(24),
            'type' => 'event',
            'attributes' => [
                'type' => $eventType, // e.g., 'payment.paid'
                'livemode' => false,
                'data' => $data,
                'created_at' => now()->timestamp,
            ],
        ];
    }
}
