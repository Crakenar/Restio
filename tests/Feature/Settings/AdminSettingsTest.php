<?php

namespace Tests\Feature\Settings;

use App\Enum\SubscriptionInterval;
use App\Enum\SubscriptionStatus;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_admin_settings(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'owner',
        ]);

        $response = $this->actingAs($user)->get('/settings/admin');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('settings/AdminSettings'));
    }

    public function test_admin_can_access_admin_settings(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get('/settings/admin');

        $response->assertOk();
    }

    public function test_employee_cannot_access_admin_settings(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'employee',
        ]);

        $response = $this->actingAs($user)->get('/settings/admin');

        $response->assertForbidden();
    }

    public function test_manager_cannot_access_admin_settings(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'manager',
        ]);

        $response = $this->actingAs($user)->get('/settings/admin');

        $response->assertForbidden();
    }

    public function test_displays_current_subscription(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'owner',
        ]);

        $subscription = Subscription::factory()->create([
            'name' => 'Premium Plan',
            'price' => 99.99,
            'currency' => 'USD',
            'interval' => SubscriptionInterval::MONTH,
        ]);

        CompanySubscription::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $response = $this->actingAs($user)->get('/settings/admin');

        $response->assertInertia(
            fn ($page) => $page
                ->has('subscription')
                ->where('subscription.plan_name', 'Premium Plan')
                ->where('subscription.status', 'active')
                ->where('subscription.price', 99.99)
        );
    }

    public function test_displays_billing_history(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'owner',
        ]);

        $subscription = Subscription::factory()->create();

        CompanySubscription::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => SubscriptionStatus::ACTIVE,
        ]);

        CompanySubscription::factory()->create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => SubscriptionStatus::CANCELLED,
        ]);

        $response = $this->actingAs($user)->get('/settings/admin');

        $response->assertInertia(
            fn ($page) => $page
                ->has('billingHistory', 2)
                ->has('billingHistory.0', fn ($item) => $item
                    ->has('id')
                    ->has('date')
                    ->has('plan_name')
                    ->has('amount')
                    ->has('currency')
                    ->has('status')
                    ->has('invoice_url')
                )
        );
    }
}
