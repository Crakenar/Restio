<?php

namespace Tests\Feature;

use App\Enum\SubscriptionInterval;
use App\Enum\SubscriptionStatus;
use App\Enum\UserRole;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_user_without_subscription_is_redirected_to_onboarding_after_registration(): void
    {
        $response = $this->post('/register', [
            'company_name' => 'Test Company',
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('onboarding'));
    }

    public function test_user_without_subscription_cannot_access_dashboard(): void
    {
        $company = Company::factory()->create();
        $team = Team::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'team_id' => $team->id,
            'role' => UserRole::OWNER,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('onboarding'));
    }

    public function test_user_without_subscription_is_redirected_to_onboarding_on_login(): void
    {
        $company = Company::factory()->create();
        $team = Team::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'team_id' => $team->id,
            'role' => UserRole::OWNER,
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('onboarding'));
    }

    public function test_onboarding_page_shows_subscription_plans(): void
    {
        $this->withoutVite();

        $company = Company::factory()->create();
        $team = Team::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'team_id' => $team->id,
            'role' => UserRole::OWNER,
        ]);

        $subscription = Subscription::factory()->create([
            'name' => 'Monthly Plan',
            'price' => 29.99,
            'interval' => SubscriptionInterval::MONTH,
        ]);

        $response = $this->actingAs($user)->get(route('onboarding'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Onboarding')
            ->has('plans', 1)
            ->has('fake_mode')
        );
    }

    public function test_user_can_create_checkout_session(): void
    {
        $company = Company::factory()->create();
        $team = Team::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'team_id' => $team->id,
            'role' => UserRole::OWNER,
        ]);

        $subscription = Subscription::factory()->create([
            'name' => 'Monthly Plan',
            'price' => 29.99,
            'interval' => SubscriptionInterval::MONTH,
        ]);

        $response = $this->actingAs($user)->postJson(route('onboarding.checkout'), [
            'plan_id' => $subscription->id,
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

    public function test_user_can_complete_onboarding_with_subscription(): void
    {
        $company = Company::factory()->create();
        $team = Team::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'team_id' => $team->id,
            'role' => UserRole::OWNER,
        ]);

        $subscription = Subscription::factory()->create([
            'name' => 'Monthly Plan',
            'price' => 29.99,
            'interval' => SubscriptionInterval::MONTH,
        ]);

        // Fake session ID from fake payment service
        $sessionId = 'cs_test_fake_'.uniqid();

        $response = $this->actingAs($user)->get(route('onboarding.complete').'?plan_id='.$subscription->id.'&session_id='.$sessionId);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('company_has_subscriptions', [
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => SubscriptionStatus::ACTIVE->value,
        ]);
    }

    public function test_user_with_active_subscription_can_access_dashboard(): void
    {
        $company = Company::factory()->create();
        $team = Team::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'team_id' => $team->id,
            'role' => UserRole::OWNER,
        ]);

        $subscription = Subscription::factory()->create();
        CompanySubscription::create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_user_with_active_subscription_is_redirected_to_dashboard_when_accessing_onboarding(): void
    {
        $company = Company::factory()->create();
        $team = Team::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'team_id' => $team->id,
            'role' => UserRole::OWNER,
        ]);

        $subscription = Subscription::factory()->create();
        CompanySubscription::create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $response = $this->actingAs($user)->get(route('onboarding'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_with_active_subscription_is_redirected_to_dashboard_on_login(): void
    {
        $company = Company::factory()->create();
        $team = Team::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'team_id' => $team->id,
            'role' => UserRole::OWNER,
            'password' => bcrypt('password'),
        ]);

        $subscription = Subscription::factory()->create();
        CompanySubscription::create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(config('fortify.home'));
    }

    public function test_onboarding_validates_plan_selection(): void
    {
        $company = Company::factory()->create();
        $team = Team::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'team_id' => $team->id,
            'role' => UserRole::OWNER,
        ]);

        $response = $this->actingAs($user)->postJson(route('onboarding.checkout'), [
            'plan_id' => 999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['plan_id']);
    }

    public function test_onboarding_requires_valid_session_id(): void
    {
        $company = Company::factory()->create();
        $team = Team::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'team_id' => $team->id,
            'role' => UserRole::OWNER,
        ]);

        $subscription = Subscription::factory()->create();

        // Try with invalid session ID
        $response = $this->actingAs($user)->get(route('onboarding.complete').'?plan_id='.$subscription->id.'&session_id=invalid_session_id');

        $response->assertRedirect(route('onboarding'));
        $response->assertSessionHas('error');
    }
}
