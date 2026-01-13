<?php

namespace App\Services;

use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class PaymentService
{
    protected bool $fakeMode;

    protected string $successUrl;

    protected string $cancelUrl;

    public function __construct()
    {
        $this->fakeMode = config('services.stripe.fake_mode', true);

        if (! $this->fakeMode) {
            Stripe::setApiKey(config('services.stripe.secret'));
        }
    }

    /**
     * Create a Stripe Checkout Session for a subscription
     */
    public function createCheckoutSession(Subscription $subscription, string $successUrl, string $cancelUrl): array
    {
        $this->successUrl = $successUrl;
        $this->cancelUrl = $cancelUrl;

        if ($this->fakeMode) {
            return $this->createFakeCheckoutSession($subscription);
        }

        return $this->createStripeCheckoutSession($subscription);
    }

    /**
     * Create a fake checkout session (for testing)
     */
    protected function createFakeCheckoutSession(Subscription $subscription): array
    {
        Log::info('Creating fake Stripe checkout session', [
            'subscription' => $subscription->name,
            'price' => $subscription->price,
        ]);

        // Generate a fake session ID
        $sessionId = 'cs_test_fake_'.uniqid();

        // Append session_id to URL (check if URL already has query params)
        $separator = str_contains($this->successUrl, '?') ? '&' : '?';
        $checkoutUrl = $this->successUrl.$separator.'session_id='.$sessionId;

        return [
            'id' => $sessionId,
            'url' => $checkoutUrl,
            'fake' => true,
        ];
    }

    /**
     * Create a real Stripe checkout session
     *
     * @throws ApiErrorException
     */
    protected function createStripeCheckoutSession(Subscription $subscription): array
    {
        $lineItems = [
            [
                'price_data' => [
                    'currency' => strtolower($subscription->currency ?? 'eur'),
                    'product_data' => [
                        'name' => $subscription->name,
                        'description' => 'Subscription to '.$subscription->name.' plan',
                    ],
                    'unit_amount' => (int) ($subscription->price * 100), // Convert to cents
                    'recurring' => $subscription->interval->value === 'one_time'
                        ? null
                        : [
                            'interval' => $subscription->interval->value,
                            'interval_count' => 1,
                        ],
                ],
                'quantity' => 1,
            ],
        ];

        $sessionData = [
            'mode' => $subscription->interval->value === 'one_time' ? 'payment' : 'subscription',
            'line_items' => $lineItems,
            'success_url' => $this->successUrl.'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->cancelUrl,
        ];

        $session = Session::create($sessionData);

        Log::info('Created real Stripe checkout session', [
            'session_id' => $session->id,
            'subscription' => $subscription->name,
        ]);

        return [
            'id' => $session->id,
            'url' => $session->url,
            'fake' => false,
        ];
    }

    /**
     * Verify a checkout session was completed
     */
    public function verifyCheckoutSession(string $sessionId): bool
    {
        if ($this->fakeMode) {
            return $this->verifyFakeCheckoutSession($sessionId);
        }

        return $this->verifyStripeCheckoutSession($sessionId);
    }

    /**
     * Verify a fake checkout session
     */
    protected function verifyFakeCheckoutSession(string $sessionId): bool
    {
        // In fake mode, all sessions starting with 'cs_test_fake_' are valid
        $isValid = str_starts_with($sessionId, 'cs_test_fake_');

        Log::info('Verifying fake checkout session', [
            'session_id' => $sessionId,
            'valid' => $isValid,
        ]);

        return $isValid;
    }

    /**
     * Verify a real Stripe checkout session
     */
    protected function verifyStripeCheckoutSession(string $sessionId): bool
    {
        try {
            $session = Session::retrieve($sessionId);

            $isValid = $session->payment_status === 'paid' || $session->status === 'complete';

            Log::info('Verified real Stripe checkout session', [
                'session_id' => $sessionId,
                'status' => $session->status,
                'payment_status' => $session->payment_status,
                'valid' => $isValid,
            ]);

            return $isValid;
        } catch (ApiErrorException $e) {
            Log::error('Failed to verify Stripe checkout session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if fake mode is enabled
     */
    public function isFakeMode(): bool
    {
        return $this->fakeMode;
    }
}
