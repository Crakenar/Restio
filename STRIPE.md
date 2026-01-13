# Stripe Payment Integration

This application uses Stripe for subscription payments with a **fake mode** for development and testing.

## Quick Start (Fake Mode - Default)

By default, the application runs in **fake mode**, which simulates Stripe payments without making real API calls. This allows you to:
- Test the complete onboarding flow without Stripe credentials
- Develop locally without worrying about test charges
- Run automated tests without external dependencies

### Current Status: ✅ Fake Mode Enabled

The app is currently in fake mode. When users "pay" for a subscription:
1. A fake Stripe session ID is generated (`cs_test_fake_...`)
2. User is immediately redirected to the success page
3. Subscription is activated without actual payment

## How It Works

### Payment Flow

1. **User selects a plan** on the onboarding page
2. **Frontend requests a checkout session**
   ```typescript
   POST /onboarding/checkout
   {
     "plan_id": 1
   }
   ```
3. **Backend creates a checkout session** (fake or real)
4. **User is redirected** to checkout URL
   - **Fake mode**: Redirects to success page immediately
   - **Real Stripe**: Redirects to Stripe Checkout
5. **Success callback** verifies payment and activates subscription
   ```
   GET /onboarding/complete?plan_id=1&session_id=cs_test_fake_abc123
   ```

### Architecture

```
┌─────────────────┐
│ Onboarding.vue  │ → User selects plan
└────────┬────────┘
         │
         ↓ POST /onboarding/checkout
┌─────────────────────────┐
│ OnboardingController    │
└────────┬────────────────┘
         │
         ↓ createCheckoutSession()
┌─────────────────────────┐
│   PaymentService        │ ← Handles fake/real mode
│                         │
│ • createFakeSession()   │ (Fake Mode)
│ • createStripeSession() │ (Real Stripe)
└────────┬────────────────┘
         │
         ↓ Returns session_id + checkout_url
┌─────────────────┐
│ User redirected │
└────────┬────────┘
         │
         ↓ GET /onboarding/complete
┌─────────────────────────┐
│ OnboardingController    │
│ • Verifies session      │
│ • Activates subscription│
└─────────────────────────┘
```

## Configuration

### Environment Variables

```bash
# Fake Mode (Default - No real Stripe needed)
STRIPE_FAKE_MODE=true

# Real Stripe Mode (Set these when ready to go live)
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_FAKE_MODE=false
```

### Config File

Configuration is in `config/services.php`:

```php
'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'fake_mode' => env('STRIPE_FAKE_MODE', true),
],
```

## Switching to Real Stripe

When you're ready to accept real payments:

### 1. Get Stripe Credentials

1. Create a [Stripe account](https://dashboard.stripe.com/register)
2. Get your API keys from the [Stripe Dashboard](https://dashboard.stripe.com/apikeys)
3. Copy your:
   - Publishable key (`pk_test_...` for test, `pk_live_...` for production)
   - Secret key (`sk_test_...` for test, `sk_live_...` for production)

### 2. Update Environment Variables

```bash
# .env
STRIPE_KEY=pk_test_your_real_key
STRIPE_SECRET=sk_test_your_real_secret
STRIPE_FAKE_MODE=false
```

### 3. Test with Stripe Test Mode

Use [Stripe test cards](https://stripe.com/docs/testing):
- Success: `4242 4242 4242 4242`
- Decline: `4000 0000 0000 0002`

### 4. Go Live

When ready for production:
1. Switch to live API keys in production `.env`
2. Enable live mode in Stripe Dashboard
3. Set up [webhooks](#webhooks-future) (optional, for advanced features)

## Payment Service API

### `PaymentService::createCheckoutSession()`

Creates a Stripe Checkout Session (or fake equivalent).

```php
$session = $paymentService->createCheckoutSession(
    $subscription,  // Subscription model
    $successUrl,    // Where to redirect on success
    $cancelUrl      // Where to redirect on cancel
);

// Returns:
[
    'id' => 'cs_test_fake_abc123',
    'url' => 'http://localhost/onboarding/complete?...',
    'fake' => true
]
```

### `PaymentService::verifyCheckoutSession()`

Verifies a checkout session was paid.

```php
$isValid = $paymentService->verifyCheckoutSession($sessionId);

// Fake mode: Checks if session_id starts with 'cs_test_fake_'
// Real mode: Queries Stripe API for payment status
```

### `PaymentService::isFakeMode()`

Check current mode.

```php
if ($paymentService->isFakeMode()) {
    // Running in fake mode
}
```

## Testing

All tests run in fake mode automatically (configured in `.env.testing`).

```bash
# Run all onboarding tests
./test.sh --filter=OnboardingFlowTest

# Test includes:
# ✓ Checkout session creation
# ✓ Payment verification
# ✓ Subscription activation
# ✓ Invalid session handling
```

## Subscription Plans

Plans are seeded in `database/seeders/SubscriptionSeeder.php`:

| Plan | Price | Interval | Stripe Mode |
|------|-------|----------|-------------|
| Monthly | €29.00 | month | subscription |
| Yearly | €290.00 | year | subscription |
| Lifetime | €999.00 | one_time | payment |

### Plan Configuration

```php
Subscription::create([
    'name' => 'Monthly',
    'slug' => 'monthly',
    'price' => 29.00,
    'currency' => 'EUR',
    'interval' => SubscriptionInterval::MONTH,
]);
```

## Webhooks (Future Enhancement)

For production, you should set up Stripe webhooks to handle:
- Subscription renewals
- Failed payments
- Subscription cancellations
- Payment disputes

**Webhook endpoint**: `/stripe/webhook` (to be implemented)

## Security Considerations

### Fake Mode
- ✅ Safe for development
- ✅ No real charges
- ❌ Don't use in production
- ✅ Properly validates session format

### Real Stripe Mode
- ✅ PCI compliant (Stripe handles card data)
- ✅ Session verification prevents fraud
- ✅ HTTPS required in production
- ✅ Webhook signature verification (when implemented)

## Troubleshooting

### "Payment verification failed"

**Fake mode**: Session ID must start with `cs_test_fake_`
```php
// Valid
cs_test_fake_abc123

// Invalid
cs_real_abc123
```

**Real mode**: Check Stripe Dashboard for session status

### "Failed to create checkout session"

1. Check `.env` configuration
2. Verify Stripe credentials (if not in fake mode)
3. Check logs: `storage/logs/laravel.log`
4. Browser console for frontend errors

### Tests Failing

```bash
# Ensure fake mode is enabled in .env.testing
STRIPE_FAKE_MODE=true

# Clear cache
php artisan config:clear
php artisan cache:clear
```

## Production Checklist

Before going live with real Stripe:

- [ ] Switch to live Stripe API keys
- [ ] Set `STRIPE_FAKE_MODE=false`
- [ ] Test with real test cards first
- [ ] Configure webhook endpoints
- [ ] Set up subscription renewal handling
- [ ] Enable HTTPS (required by Stripe)
- [ ] Review Stripe Dashboard settings
- [ ] Test failed payment scenarios
- [ ] Set up proper error logging
- [ ] Add customer support email to Stripe

## Resources

- [Stripe Documentation](https://stripe.com/docs)
- [Stripe PHP Library](https://github.com/stripe/stripe-php)
- [Stripe Checkout](https://stripe.com/docs/payments/checkout)
- [Test Cards](https://stripe.com/docs/testing)
- [Webhooks Guide](https://stripe.com/docs/webhooks)
