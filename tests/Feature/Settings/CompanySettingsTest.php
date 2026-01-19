<?php

namespace Tests\Feature\Settings;

use App\Enum\SubscriptionStatus;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\CompanySubscription;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a company with an active subscription.
     */
    private function createCompanyWithSubscription(): Company
    {
        $company = Company::factory()->create();
        $subscription = Subscription::factory()->create();

        CompanySubscription::create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => null,
        ]);

        return $company;
    }

    /**
     * Test that owner can access company settings page.
     */
    public function test_owner_can_access_company_settings(): void
    {
        $company = $this->createCompanyWithSubscription();
        $owner = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'owner',
        ]);

        $this->actingAs($owner);

        $response = $this->get('/settings/company');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('settings/CompanySettings')
            ->has('company')
            ->has('settings')
        );
    }

    /**
     * Test that admin can access company settings page.
     */
    public function test_admin_can_access_company_settings(): void
    {
        $company = $this->createCompanyWithSubscription();
        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $response = $this->get('/settings/company');

        $response->assertStatus(200);
    }

    /**
     * Test that manager cannot access company settings page.
     */
    public function test_manager_cannot_access_company_settings(): void
    {
        $company = $this->createCompanyWithSubscription();
        $manager = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'manager',
        ]);

        $this->actingAs($manager);

        $response = $this->get('/settings/company');

        $response->assertStatus(403);
    }

    /**
     * Test that employee cannot access company settings page.
     */
    public function test_employee_cannot_access_company_settings(): void
    {
        $company = $this->createCompanyWithSubscription();
        $employee = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'employee',
        ]);

        $this->actingAs($employee);

        $response = $this->get('/settings/company');

        $response->assertStatus(403);
    }

    /**
     * Test that owner can update company settings.
     */
    public function test_owner_can_update_company_settings(): void
    {
        $company = $this->createCompanyWithSubscription();
        $owner = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'owner',
        ]);

        CompanySetting::create([
            'company_id' => $company->id,
            'annual_days' => 20,
            'approval_required' => true,
        ]);

        $this->actingAs($owner);

        $response = $this->post('/settings/company', [
            'name' => 'Updated Company Name',
            'annual_days' => 25,
            'approval_required' => false,
            'timezone' => 'America/New_York',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Updated Company Name',
            'timezone' => 'America/New_York',
        ]);

        $this->assertDatabaseHas('company_settings', [
            'company_id' => $company->id,
            'annual_days' => 25,
            'approval_required' => false,
        ]);
    }

    /**
     * Test that admin can update company settings.
     */
    public function test_admin_can_update_company_settings(): void
    {
        $company = $this->createCompanyWithSubscription();
        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $response = $this->post('/settings/company', [
            'name' => 'Admin Updated Company',
            'annual_days' => 30,
            'approval_required' => true,
            'timezone' => 'Europe/London',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test that manager cannot update company settings.
     */
    public function test_manager_cannot_update_company_settings(): void
    {
        $company = $this->createCompanyWithSubscription();
        $manager = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'manager',
        ]);

        $this->actingAs($manager);

        $response = $this->post('/settings/company', [
            'name' => 'Hacked Company',
            'annual_days' => 100,
            'approval_required' => false,
            'timezone' => 'UTC',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test validation for company name.
     */
    public function test_company_name_is_required(): void
    {
        $company = $this->createCompanyWithSubscription();
        $owner = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'owner',
        ]);

        $this->actingAs($owner);

        $response = $this->post('/settings/company', [
            'name' => '',
            'annual_days' => 20,
            'approval_required' => true,
            'timezone' => 'UTC',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /**
     * Test validation for annual days.
     */
    public function test_annual_days_must_be_valid_number(): void
    {
        $company = $this->createCompanyWithSubscription();
        $owner = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'owner',
        ]);

        $this->actingAs($owner);

        // Test negative number
        $response = $this->post('/settings/company', [
            'name' => 'Test Company',
            'annual_days' => -5,
            'approval_required' => true,
            'timezone' => 'UTC',
        ]);

        $response->assertSessionHasErrors(['annual_days']);

        // Test exceeds maximum
        $response = $this->post('/settings/company', [
            'name' => 'Test Company',
            'annual_days' => 400,
            'approval_required' => true,
            'timezone' => 'UTC',
        ]);

        $response->assertSessionHasErrors(['annual_days']);
    }

    /**
     * Test validation for timezone.
     */
    public function test_timezone_must_be_valid(): void
    {
        $company = $this->createCompanyWithSubscription();
        $owner = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'owner',
        ]);

        $this->actingAs($owner);

        $response = $this->post('/settings/company', [
            'name' => 'Test Company',
            'annual_days' => 20,
            'approval_required' => true,
            'timezone' => 'Invalid/Timezone',
        ]);

        $response->assertSessionHasErrors(['timezone']);
    }

    /**
     * Test that company settings are created if they don't exist.
     */
    public function test_company_settings_are_created_if_not_exist(): void
    {
        $company = $this->createCompanyWithSubscription();
        $owner = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'owner',
        ]);

        // Ensure no settings exist
        $this->assertDatabaseMissing('company_settings', [
            'company_id' => $company->id,
        ]);

        $this->actingAs($owner);

        // Access the page (should create default settings)
        $response = $this->get('/settings/company');

        $response->assertStatus(200);

        // Settings should now exist with defaults
        $this->assertDatabaseHas('company_settings', [
            'company_id' => $company->id,
            'annual_days' => 20,
            'approval_required' => true,
        ]);
    }
}
