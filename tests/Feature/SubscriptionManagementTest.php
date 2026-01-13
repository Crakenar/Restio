<?php

namespace Tests\Feature;

use App\Enum\SubscriptionInterval;
use App\Enum\SubscriptionStatus;
use App\Enum\UserRole;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Department;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createOwnerWithActiveSubscription(): array
    {
        $company = Company::factory()->create();
        $department = Department::factory()->create(['company_id' => $company->id]);
        $owner = User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'role' => UserRole::OWNER,
        ]);

        $subscription = Subscription::factory()->create([
            'name' => 'Monthly Plan',
            'price' => 29.99,
            'interval' => SubscriptionInterval::MONTH,
        ]);

        CompanySubscription::create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        return [$owner, $company, $subscription];
    }

    public function test_owner_can_access_subscription_management_page(): void
    {
        $this->withoutVite();

        [$owner] = $this->createOwnerWithActiveSubscription();

        $response = $this->actingAs($owner)->get(route('subscription.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('SubscriptionManagement')
            ->has('company')
            ->has('current_subscription')
            ->has('subscription_history')
            ->has('available_plans')
            ->has('fake_mode')
        );
    }

    public function test_non_owner_cannot_access_subscription_management_page(): void
    {
        $company = Company::factory()->create();
        $department = Department::factory()->create(['company_id' => $company->id]);
        $admin = User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'role' => UserRole::ADMIN,
        ]);

        $subscription = Subscription::factory()->create();
        CompanySubscription::create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $response = $this->actingAs($admin)->get(route('subscription.index'));

        $response->assertForbidden();
    }

    public function test_owner_can_create_checkout_session_for_plan_change(): void
    {
        [$owner] = $this->createOwnerWithActiveSubscription();

        $newPlan = Subscription::factory()->create([
            'name' => 'Yearly Plan',
            'price' => 299.99,
            'interval' => SubscriptionInterval::YEAR,
        ]);

        $response = $this->actingAs($owner)->postJson(route('subscription.change'), [
            'plan_id' => $newPlan->id,
        ]);

        $response->assertOk();
        $response->assertJson([
            'fake' => true,
        ]);
        $response->assertJsonStructure([
            'session_id',
            'checkout_url',
            'fake',
        ]);
    }

    public function test_non_owner_cannot_change_plan(): void
    {
        $company = Company::factory()->create();
        $department = Department::factory()->create(['company_id' => $company->id]);
        $employee = User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'role' => UserRole::EMPLOYEE,
        ]);

        $subscription = Subscription::factory()->create();
        CompanySubscription::create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $newPlan = Subscription::factory()->create();

        $response = $this->actingAs($employee)->postJson(route('subscription.change'), [
            'plan_id' => $newPlan->id,
        ]);

        $response->assertForbidden();
    }

    public function test_owner_cannot_change_to_same_plan(): void
    {
        [$owner, $company, $subscription] = $this->createOwnerWithActiveSubscription();

        $response = $this->actingAs($owner)->post(route('subscription.change'), [
            'plan_id' => $subscription->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_owner_can_complete_plan_upgrade(): void
    {
        [$owner, $company, $oldPlan] = $this->createOwnerWithActiveSubscription();

        $newPlan = Subscription::factory()->create([
            'name' => 'Yearly Plan',
            'price' => 299.99,
            'interval' => SubscriptionInterval::YEAR,
        ]);

        $sessionId = 'cs_test_fake_'.uniqid();

        $response = $this->actingAs($owner)->get(
            route('subscription.upgrade.complete').'?plan_id='.$newPlan->id.'&session_id='.$sessionId
        );

        $response->assertRedirect(route('subscription.index'));
        $response->assertSessionHas('success');

        // Check old subscription is cancelled
        $this->assertDatabaseHas('company_has_subscriptions', [
            'company_id' => $company->id,
            'subscription_id' => $oldPlan->id,
            'status' => SubscriptionStatus::CANCELLED->value,
        ]);

        // Check new subscription is active
        $this->assertDatabaseHas('company_has_subscriptions', [
            'company_id' => $company->id,
            'subscription_id' => $newPlan->id,
            'status' => SubscriptionStatus::ACTIVE->value,
        ]);
    }

    public function test_plan_upgrade_fails_with_invalid_session(): void
    {
        [$owner] = $this->createOwnerWithActiveSubscription();

        $newPlan = Subscription::factory()->create();

        $response = $this->actingAs($owner)->get(
            route('subscription.upgrade.complete').'?plan_id='.$newPlan->id.'&session_id=invalid_session'
        );

        $response->assertRedirect(route('subscription.index'));
        $response->assertSessionHas('error');
    }

    public function test_owner_can_cancel_subscription(): void
    {
        [$owner, $company, $subscription] = $this->createOwnerWithActiveSubscription();

        $response = $this->actingAs($owner)->post(route('subscription.cancel'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('company_has_subscriptions', [
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => SubscriptionStatus::CANCELLED->value,
        ]);
    }

    public function test_non_owner_cannot_cancel_subscription(): void
    {
        $company = Company::factory()->create();
        $department = Department::factory()->create(['company_id' => $company->id]);
        $manager = User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'role' => UserRole::MANAGER,
        ]);

        $subscription = Subscription::factory()->create();
        CompanySubscription::create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $response = $this->actingAs($manager)->post(route('subscription.cancel'));

        $response->assertForbidden();
    }

    public function test_cancel_subscription_without_active_subscription_fails(): void
    {
        $company = Company::factory()->create();
        $department = Department::factory()->create(['company_id' => $company->id]);
        $owner = User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'role' => UserRole::OWNER,
        ]);

        $response = $this->actingAs($owner)->post(route('subscription.cancel'));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_subscription_management_validates_plan_id(): void
    {
        [$owner] = $this->createOwnerWithActiveSubscription();

        $response = $this->actingAs($owner)->postJson(route('subscription.change'), [
            'plan_id' => 999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['plan_id']);
    }
}
