<?php

namespace Tests\Concerns;

use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Subscription;

trait CreatesSubscriptions
{
    /**
     * Create an active subscription for a company
     */
    protected function createActiveSubscription(Company $company, string $plan = 'monthly'): CompanySubscription
    {
        // Ensure subscription plan exists
        $subscription = Subscription::where('slug', $plan)->first();

        if (! $subscription) {
            $subscription = Subscription::factory()->create([
                'slug' => $plan,
                'name' => ucfirst($plan).' Plan',
                'price' => $plan === 'monthly' ? 29.99 : 299.99,
            ]);
        }

        // Create active company subscription
        return CompanySubscription::create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => null,
            'stripe_subscription_id' => 'test_sub_'.uniqid(),
        ]);
    }

    /**
     * Create a trialing subscription for a company
     */
    protected function createTrialingSubscription(Company $company, string $plan = 'monthly'): CompanySubscription
    {
        $subscription = Subscription::firstOrCreate(
            ['slug' => $plan],
            [
                'name' => ucfirst($plan).' Plan',
                'price' => $plan === 'monthly' ? 29.99 : 299.99,
            ]
        );

        return CompanySubscription::create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => 'trialing',
            'starts_at' => now(),
            'ends_at' => now()->addDays(14),
            'stripe_subscription_id' => 'test_sub_'.uniqid(),
        ]);
    }

    /**
     * Create a canceled subscription for a company
     */
    protected function createCanceledSubscription(Company $company, string $plan = 'monthly'): CompanySubscription
    {
        $subscription = Subscription::firstOrCreate(
            ['slug' => $plan],
            [
                'name' => ucfirst($plan).' Plan',
                'price' => $plan === 'monthly' ? 29.99 : 299.99,
            ]
        );

        return CompanySubscription::create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => 'canceled',
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addDays(30),
            'stripe_subscription_id' => 'test_sub_'.uniqid(),
        ]);
    }

    /**
     * Ensure company has an active subscription (convenience method)
     */
    protected function ensureHasSubscription(Company $company): CompanySubscription
    {
        return $this->createActiveSubscription($company);
    }
}
