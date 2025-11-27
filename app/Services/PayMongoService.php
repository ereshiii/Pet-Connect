<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayMongoService
{
    private string $secretKey;
    private string $publicKey;
    private string $baseUrl;
    private bool $useMock;
    private ?MockPayMongoService $mockService = null;

    public function __construct()
    {
        $this->secretKey = config('paymongo.secret_key');
        $this->publicKey = config('paymongo.public_key');
        $this->baseUrl = config('paymongo.base_url');
        
        // Use mock if keys not configured or mock mode enabled
        $this->useMock = empty($this->secretKey) 
            || str_contains($this->secretKey, 'your_secret_key_here')
            || config('paymongo.mock_mode', false);
            
        if ($this->useMock) {
            $this->mockService = new MockPayMongoService();
            Log::info('💳 PayMongo: Using MOCK mode for payments (no real API calls)');
        }
    }

    /**
     * Create a payment intent for card payments
     */
    public function createPaymentIntent(int $amount, string $currency = 'PHP', array $metadata = []): array
    {
        if ($this->useMock) {
            return $this->mockService->createPaymentIntent($amount, $metadata);
        }
        
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->baseUrl}/payment_intents", [
                    'data' => [
                        'attributes' => [
                            'amount' => $amount * 100, // Convert to centavos
                            'payment_method_allowed' => ['card'],
                            'payment_method_options' => [
                                'card' => ['request_three_d_secure' => 'any']
                            ],
                            'currency' => $currency,
                            'description' => $metadata['description'] ?? 'Subscription Payment',
                            'statement_descriptor' => 'PetConnect',
                            'metadata' => $metadata,
                        ]
                    ]
                ]);

            if ($response->failed()) {
                throw new Exception('Failed to create payment intent: ' . $response->body());
            }

            return $response->json()['data'];
        } catch (Exception $e) {
            Log::error('PayMongo Payment Intent Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a payment method (for card tokenization)
     */
    public function createPaymentMethod(array $cardDetails): array
    {
        try {
            $response = Http::withBasicAuth($this->publicKey, '')
                ->post("{$this->baseUrl}/payment_methods", [
                    'data' => [
                        'attributes' => [
                            'type' => 'card',
                            'details' => $cardDetails
                        ]
                    ]
                ]);

            if ($response->failed()) {
                throw new Exception('Failed to create payment method: ' . $response->body());
            }

            return $response->json()['data'];
        } catch (Exception $e) {
            Log::error('PayMongo Payment Method Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Attach payment intent to payment method
     */
    public function attachPaymentIntent(string $paymentIntentId, string $paymentMethodId, ?string $returnUrl = null, float $amount = 0): array
    {
        if ($this->useMock) {
            return $this->mockService->attachPaymentIntent($paymentIntentId, $paymentMethodId, $amount);
        }
        
        try {
            $payload = [
                'data' => [
                    'attributes' => [
                        'payment_method' => $paymentMethodId,
                    ]
                ]
            ];

            if ($returnUrl) {
                $payload['data']['attributes']['return_url'] = $returnUrl;
            }

            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->baseUrl}/payment_intents/{$paymentIntentId}/attach", $payload);

            if ($response->failed()) {
                throw new Exception('Failed to attach payment intent: ' . $response->body());
            }

            return $response->json()['data'];
        } catch (Exception $e) {
            Log::error('PayMongo Attach Intent Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a source for e-wallet payments (GCash, GrabPay, etc.)
     */
    public function createSource(string $type, int $amount, array $billing, string $redirectUrl, array $metadata = []): array
    {
        if ($this->useMock) {
            return $this->mockService->createSource($type, $amount, $billing, $redirectUrl, $metadata);
        }
        
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->baseUrl}/sources", [
                    'data' => [
                        'attributes' => [
                            'type' => $type, // 'gcash', 'grab_pay', 'paymaya'
                            'amount' => $amount * 100, // Convert to centavos
                            'currency' => 'PHP',
                            'redirect' => [
                                'success' => $redirectUrl . '?status=success',
                                'failed' => $redirectUrl . '?status=failed',
                            ],
                            'billing' => $billing,
                            'metadata' => $metadata,
                        ]
                    ]
                ]);

            if ($response->failed()) {
                throw new Exception('Failed to create source: ' . $response->body());
            }

            return $response->json()['data'];
        } catch (Exception $e) {
            Log::error('PayMongo Source Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a payment using source (for e-wallets)
     */
    public function createPayment(string $sourceId, int $amount, string $currency = 'PHP', array $metadata = []): array
    {
        if ($this->useMock) {
            return $this->mockService->createPayment($sourceId, $amount, $currency, $metadata);
        }
        
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->baseUrl}/payments", [
                    'data' => [
                        'attributes' => [
                            'amount' => $amount * 100,
                            'currency' => $currency,
                            'source' => [
                                'id' => $sourceId,
                                'type' => 'source'
                            ],
                            'description' => $metadata['description'] ?? 'Subscription Payment',
                            'statement_descriptor' => 'PetConnect',
                            'metadata' => $metadata,
                        ]
                    ]
                ]);

            if ($response->failed()) {
                throw new Exception('Failed to create payment: ' . $response->body());
            }

            return $response->json()['data'];
        } catch (Exception $e) {
            Log::error('PayMongo Payment Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Retrieve a payment intent
     */
    public function retrievePaymentIntent(string $paymentIntentId): array
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->get("{$this->baseUrl}/payment_intents/{$paymentIntentId}");

            if ($response->failed()) {
                throw new Exception('Failed to retrieve payment intent: ' . $response->body());
            }

            return $response->json()['data'];
        } catch (Exception $e) {
            Log::error('PayMongo Retrieve Intent Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Retrieve a source
     */
    public function retrieveSource(string $sourceId): array
    {
        if ($this->useMock) {
            return $this->mockService->retrieveSource($sourceId);
        }
        
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->get("{$this->baseUrl}/sources/{$sourceId}");

            if ($response->failed()) {
                throw new Exception('Failed to retrieve source: ' . $response->body());
            }

            return $response->json()['data'];
        } catch (Exception $e) {
            Log::error('PayMongo Retrieve Source Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, array $headers): bool
    {
        if ($this->useMock) {
            return $this->mockService->verifyWebhookSignature([], $headers);
        }
        
        try {
            $webhookSecret = config('paymongo.webhook_secret');
            
            if (!isset($headers['paymongo-signature'])) {
                return false;
            }

            $signatures = [];
            foreach (explode(',', $headers['paymongo-signature']) as $item) {
                [$key, $value] = explode('=', $item, 2);
                $signatures[trim($key)] = trim($value);
            }

            if (!isset($signatures['t']) || !isset($signatures['li']) || !isset($signatures['te'])) {
                return false;
            }

            $timestamp = $signatures['t'];
            $testSignature = $signatures['te'];
            $liveSignature = $signatures['li'];

            // Compute expected signature
            $signedPayload = $timestamp . '.' . $payload;
            $expectedSignature = hash_hmac('sha256', $signedPayload, $webhookSecret);

            // Check if either test or live signature matches
            return hash_equals($expectedSignature, $testSignature) || 
                   hash_equals($expectedSignature, $liveSignature);
        } catch (Exception $e) {
            Log::error('PayMongo Webhook Verification Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get payment methods for display
     */
    public function getAvailablePaymentMethods(): array
    {
        if ($this->useMock) {
            return $this->mockService->getAvailablePaymentMethods();
        }
        
        return config('paymongo.payment_methods', [
            'card' => [
                'name' => 'Credit/Debit Card',
                'fee_percentage' => 3.5,
                'fee_fixed' => 15,
                'type' => 'card'
            ],
            'gcash' => [
                'name' => 'GCash',
                'fee_percentage' => 2.5,
                'fee_fixed' => 15,
                'type' => 'source'
            ],
            'grab_pay' => [
                'name' => 'GrabPay',
                'fee_percentage' => 2.5,
                'fee_fixed' => 15,
                'type' => 'source'
            ],
            'paymaya' => [
                'name' => 'Maya',
                'fee_percentage' => 3.5,
                'fee_fixed' => 15,
                'type' => 'source'
            ],
        ]);
    }

    /**
     * Calculate total with fees
     */
    public function calculateTotalWithFees(float $amount, string $paymentMethod): array
    {
        if ($this->useMock) {
            return $this->mockService->calculateTotalWithFees($amount, $paymentMethod);
        }
        
        $methods = $this->getAvailablePaymentMethods();
        
        // Find the method in the array
        $method = null;
        foreach ($methods as $m) {
            if ($m['type'] === $paymentMethod) {
                $method = $m;
                break;
            }
        }
        
        if (!$method) {
            throw new Exception('Invalid payment method');
        }

        $feePercentage = $method['fee_percentage'] / 100;
        $feeFixed = $method['fee_fixed'];

        $fee = ($amount * $feePercentage) + $feeFixed;
        $total = $amount + $fee;

        return [
            'amount' => $amount,
            'fee' => round($fee, 2),
            'total' => round($total, 2),
        ];
    }
}
