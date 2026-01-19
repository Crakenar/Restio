<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Stripe\BillingPortal\Session as BillingPortalSession;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
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
    public function createCheckoutSession(Subscription $subscription, string $successUrl, string $cancelUrl, ?string $customerId = null): array
    {
        $this->successUrl = $successUrl;
        $this->cancelUrl = $cancelUrl;

        if ($this->fakeMode) {
            return $this->createFakeCheckoutSession($subscription);
        }

        return $this->createStripeCheckoutSession($subscription, $customerId);
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
    protected function createStripeCheckoutSession(Subscription $subscription, ?string $customerId = null): array
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

        // Add customer ID if provided
        if ($customerId) {
            $sessionData['customer'] = $customerId;
        }

        $session = Session::create($sessionData);

        Log::info('Created real Stripe checkout session', [
            'session_id' => $session->id,
            'subscription' => $subscription->name,
            'customer_id' => $customerId,
        ]);

        return [
            'id' => $session->id,
            'url' => $session->url,
            'fake' => false,
        ];
    }

    /**
     * Verify a checkout session was completed and return session details
     */
    public function verifyCheckoutSession(string $sessionId): bool
    {
        if ($this->fakeMode) {
            return $this->verifyFakeCheckoutSession($sessionId);
        }

        return $this->verifyStripeCheckoutSession($sessionId);
    }

    /**
     * Get checkout session details including subscription and invoice IDs
     */
    public function getCheckoutSessionDetails(string $sessionId): ?array
    {
        if ($this->fakeMode) {
            return $this->getFakeCheckoutSessionDetails($sessionId);
        }

        return $this->getStripeCheckoutSessionDetails($sessionId);
    }

    /**
     * Get fake checkout session details
     */
    protected function getFakeCheckoutSessionDetails(string $sessionId): array
    {
        return [
            'subscription_id' => 'sub_fake_'.uniqid(),
            'invoice_id' => 'in_fake_'.uniqid(),
        ];
    }

    /**
     * Get real Stripe checkout session details
     */
    protected function getStripeCheckoutSessionDetails(string $sessionId): ?array
    {
        try {
            $session = Session::retrieve([
                'id' => $sessionId,
                'expand' => ['subscription', 'invoice'],
            ]);

            Log::info('Retrieved Stripe checkout session details', [
                'session_id' => $sessionId,
                'subscription_id' => $session->subscription,
                'invoice_id' => $session->invoice,
            ]);

            return [
                'subscription_id' => is_string($session->subscription) ? $session->subscription : $session->subscription?->id,
                'invoice_id' => is_string($session->invoice) ? $session->invoice : $session->invoice?->id,
            ];
        } catch (ApiErrorException $e) {
            Log::error('Failed to retrieve Stripe checkout session details', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
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

    /**
     * Create or retrieve a Stripe customer for a company
     */
    public function createOrRetrieveCustomer(Company $company): ?string
    {
        if ($this->fakeMode) {
            return $this->createFakeCustomer($company);
        }

        return $this->createStripeCustomer($company);
    }

    /**
     * Create a fake Stripe customer ID
     */
    protected function createFakeCustomer(Company $company): string
    {
        if ($company->stripe_customer_id) {
            return $company->stripe_customer_id;
        }

        $customerId = 'cus_fake_'.uniqid();

        $company->update(['stripe_customer_id' => $customerId]);

        Log::info('Created fake Stripe customer', [
            'company_id' => $company->id,
            'customer_id' => $customerId,
        ]);

        return $customerId;
    }

    /**
     * Create a real Stripe customer
     *
     * @throws ApiErrorException
     */
    protected function createStripeCustomer(Company $company): ?string
    {
        if ($company->stripe_customer_id) {
            return $company->stripe_customer_id;
        }

        try {
            $customer = Customer::create([
                'name' => $company->name,
                'metadata' => [
                    'company_id' => $company->id,
                ],
            ]);

            $company->update(['stripe_customer_id' => $customer->id]);

            Log::info('Created real Stripe customer', [
                'company_id' => $company->id,
                'customer_id' => $customer->id,
            ]);

            return $customer->id;
        } catch (ApiErrorException $e) {
            Log::error('Failed to create Stripe customer', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate Stripe customer portal URL
     */
    public function createCustomerPortalUrl(Company $company, string $returnUrl): ?string
    {
        if ($this->fakeMode) {
            return $this->createFakePortalUrl($returnUrl);
        }

        return $this->createStripePortalUrl($company, $returnUrl);
    }

    /**
     * Create a fake portal URL
     */
    protected function createFakePortalUrl(string $returnUrl): string
    {
        // In fake mode, just return to the subscription management page
        return route('subscription.index');
    }

    /**
     * Create a real Stripe customer portal URL
     *
     * @throws ApiErrorException
     */
    protected function createStripePortalUrl(Company $company, string $returnUrl): ?string
    {
        if (! $company->stripe_customer_id) {
            Log::error('Cannot create portal URL: Company has no Stripe customer ID', [
                'company_id' => $company->id,
            ]);

            return null;
        }

        try {
            $session = BillingPortalSession::create([
                'customer' => $company->stripe_customer_id,
                'return_url' => $returnUrl,
            ]);

            Log::info('Created Stripe customer portal session', [
                'company_id' => $company->id,
                'customer_id' => $company->stripe_customer_id,
            ]);

            return $session->url;
        } catch (ApiErrorException $e) {
            Log::error('Failed to create Stripe portal session', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Retrieve invoice URL from Stripe
     */
    public function getInvoiceUrl(?string $invoiceId): ?string
    {
        if (! $invoiceId || $this->fakeMode) {
            return null;
        }

        return $this->getStripeInvoiceUrl($invoiceId);
    }

    /**
     * Get real Stripe invoice URL
     */
    protected function getStripeInvoiceUrl(string $invoiceId): ?string
    {
        try {
            $invoice = Invoice::retrieve($invoiceId);

            Log::info('Retrieved Stripe invoice', [
                'invoice_id' => $invoiceId,
                'invoice_pdf' => $invoice->invoice_pdf,
            ]);

            return $invoice->invoice_pdf;
        } catch (ApiErrorException $e) {
            Log::error('Failed to retrieve Stripe invoice', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
