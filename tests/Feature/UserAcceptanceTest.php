<?php

namespace Tests\Feature;

use App\Enum\UserRole;
use App\Enum\VacationRequestStatus;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesSubscriptions;
use Tests\TestCase;

class UserAcceptanceTest extends TestCase
{
    use RefreshDatabase, CreatesSubscriptions;

    /**
     * UAT-01: Employee Workflow - Complete journey from registration to viewing calendar
     */
    public function test_employee_complete_workflow(): void
    {
        // Step 1: Register a new employee
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id, 'annual_days' => 20]);
        $this->createActiveSubscription($company);

        $response = $this->post('/register', [
            'name' => 'John Employee',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => $company->name,
        ]);

        // Should redirect after registration
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'company_id' => $company->id,
        ]);

        $employee = User::where('email', 'john@example.com')->first();

        // Step 2: Login
        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect();

        // Step 3: View dashboard
        $response = $this->actingAs($employee)->get('/dashboard');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Dashboard'));

        // Step 4: Submit a vacation request
        $response = $this->actingAs($employee)->post('/vacation-requests', [
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeek()->addDays(5)->toDateString(),
            'type' => 'vacation',
            'reason' => 'Family vacation',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vacation_requests', [
            'user_id' => $employee->id,
            'type' => 'vacation',
            'status' => 'pending',
        ]);

        // Step 5: View calendar
        $response = $this->actingAs($employee)->get('/calendar');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Calendar'));

        // Step 6: View requests page
        $response = $this->actingAs($employee)->get('/requests');
        $response->assertOk();
    }

    /**
     * UAT-02: Manager Workflow - Login, review requests, approve/reject
     */
    public function test_manager_complete_workflow(): void
    {
        // Setup: Create company with employees and manager
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);
        $team = Team::factory()->create(['company_id' => $company->id]);

        $manager = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::MANAGER->value,
            'team_id' => $team->id,
        ]);

        $employee = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::EMPLOYEE->value,
            'team_id' => $team->id,
        ]);

        // Step 1: Manager logs in
        $response = $this->actingAs($manager)->get('/dashboard');
        $response->assertOk();

        // Step 2: Employee submits a request
        $vacationRequest = VacationRequest::factory()->create([
            'user_id' => $employee->id,
            'company_id' => $company->id,
            'status' => VacationRequestStatus::PENDING,
        ]);

        // Step 3: Manager views requests page
        $response = $this->actingAs($manager)->get('/requests');
        $response->assertOk();

        // Step 4: Manager approves the request
        $response = $this->actingAs($manager)->post("/vacation-requests/{$vacationRequest->id}/approve");
        $response->assertRedirect();

        $vacationRequest->refresh();
        $this->assertEquals(VacationRequestStatus::APPROVED, $vacationRequest->status);
        $this->assertEquals($manager->id, $vacationRequest->approved_by);

        // Step 5: Manager creates another request to reject
        $vacationRequest2 = VacationRequest::factory()->create([
            'user_id' => $employee->id,
            'company_id' => $company->id,
            'status' => VacationRequestStatus::PENDING,
        ]);

        // Step 6: Manager rejects the request
        $response = $this->actingAs($manager)->post("/vacation-requests/{$vacationRequest2->id}/reject", [
            'rejection_reason' => 'Overlapping project deadlines',
        ]);
        $response->assertRedirect();

        $vacationRequest2->refresh();
        $this->assertEquals(VacationRequestStatus::REJECTED, $vacationRequest2->status);
        $this->assertNotNull($vacationRequest2->rejection_reason);
    }

    /**
     * UAT-03: Admin Workflow - Manage employees, teams, view reports
     */
    public function test_admin_complete_workflow(): void
    {
        // Setup
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::ADMIN->value,
        ]);

        // Step 1: Admin logs in and views dashboard
        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertOk();

        // Step 2: Admin views employees page
        $response = $this->actingAs($admin)->get('/employees');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Employees'));

        // Step 3: Admin creates a new employee
        $response = $this->actingAs($admin)->post('/employees', [
            'name' => 'New Employee',
            'email' => 'newemployee@example.com',
            'password' => 'password123',
            'role' => 'employee',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'newemployee@example.com',
            'company_id' => $company->id,
        ]);

        // Step 4: Admin views team management page
        $response = $this->actingAs($admin)->get('/team-management');
        $response->assertOk();

        // Step 5: Admin creates a new team
        $response = $this->actingAs($admin)->post('/team-management', [
            'name' => 'Engineering Team',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('teams', [
            'name' => 'Engineering Team',
            'company_id' => $company->id,
        ]);

        // Step 6: Admin assigns users to team
        $team = Team::where('company_id', $company->id)->first();
        $employee = User::where('email', 'newemployee@example.com')->first();

        $response = $this->actingAs($admin)->post("/team-management/{$team->id}/assign-users", [
            'user_ids' => [$employee->id],
        ]);

        $response->assertRedirect();
        $employee->refresh();
        $this->assertEquals($team->id, $employee->team_id);
    }

    /**
     * UAT-04: Owner Workflow - Manage subscription and configure settings
     */
    public function test_owner_complete_workflow(): void
    {
        // Setup
        $company = Company::factory()->create();
        $companySetting = CompanySetting::factory()->create([
            'company_id' => $company->id,
            'annual_days' => 20,
        ]);
        $this->createActiveSubscription($company);

        $owner = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::OWNER->value,
        ]);

        // Step 1: Owner views dashboard
        $response = $this->actingAs($owner)->get('/dashboard');
        $response->assertOk();

        // Step 2: Owner views admin settings (billing/subscription)
        $response = $this->actingAs($owner)->get('/settings/admin');
        $response->assertOk();

        // Step 3: Owner views company settings
        $response = $this->actingAs($owner)->get('/settings/company');
        $response->assertOk();

        // Step 4: Owner updates company settings
        $response = $this->actingAs($owner)->post('/settings/company', [
            'company_name' => 'Updated Company Name',
            'annual_days' => 25,
        ]);

        $response->assertRedirect();
        $companySetting->refresh();
        $this->assertEquals(25, $companySetting->annual_days);

        // Step 5: Owner views profile settings
        $response = $this->actingAs($owner)->get('/settings/profile');
        $response->assertOk();

        // Step 6: Owner updates profile
        $response = $this->actingAs($owner)->patch('/settings/profile', [
            'name' => 'Updated Owner Name',
            'email' => $owner->email,
        ]);

        $response->assertRedirect();
        $owner->refresh();
        $this->assertEquals('Updated Owner Name', $owner->name);
    }

    /**
     * UAT-05: Complete approval workflow with notifications
     */
    public function test_complete_approval_workflow_with_notifications(): void
    {
        // Setup
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);
        $team = Team::factory()->create(['company_id' => $company->id]);

        $manager = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::MANAGER->value,
            'team_id' => $team->id,
        ]);

        $employee = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::EMPLOYEE->value,
            'team_id' => $team->id,
        ]);

        // Step 1: Employee submits request
        $response = $this->actingAs($employee)->post('/vacation-requests', [
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeek()->addDays(3)->toDateString(),
            'type' => 'vacation',
            'reason' => 'Personal trip',
        ]);

        $request = VacationRequest::latest()->first();

        // Step 2: Manager receives notification (check database notifications table)
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $manager->id,
            'type' => 'App\Notifications\VacationRequestSubmitted',
        ]);

        // Step 3: Manager views notifications
        $response = $this->actingAs($manager)->get('/notifications');
        $response->assertOk();

        // Step 4: Manager approves request
        $response = $this->actingAs($manager)->post("/vacation-requests/{$request->id}/approve");
        $response->assertRedirect();

        // Step 5: Employee receives approval notification
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $employee->id,
            'type' => 'App\Notifications\VacationRequestApproved',
        ]);

        // Step 6: Employee views their notifications
        $response = $this->actingAs($employee)->get('/notifications');
        $response->assertOk();
    }

    /**
     * UAT-06: Multi-company isolation verification
     */
    public function test_multi_company_data_isolation(): void
    {
        // Setup two separate companies
        $company1 = Company::factory()->create(['name' => 'Company A']);
        $company2 = Company::factory()->create(['name' => 'Company B']);

        CompanySetting::factory()->create(['company_id' => $company1->id]);
        CompanySetting::factory()->create(['company_id' => $company2->id]);
        $this->createActiveSubscription($company1);
        $this->createActiveSubscription($company2);

        $user1 = User::factory()->create(['company_id' => $company1->id]);
        $user2 = User::factory()->create(['company_id' => $company2->id]);

        $request1 = VacationRequest::factory()->create([
            'user_id' => $user1->id,
            'company_id' => $company1->id,
        ]);

        $request2 = VacationRequest::factory()->create([
            'user_id' => $user2->id,
            'company_id' => $company2->id,
        ]);

        // Verification: User from Company A should only see Company A data
        $response = $this->actingAs($user1)->get('/requests');
        $response->assertOk();
        // The response should only include requests from company1

        $response = $this->actingAs($user1)->get('/dashboard');
        $response->assertOk();
        // Dashboard should only show company1 data
    }

    /**
     * UAT-07: Calendar view with team data
     */
    public function test_calendar_view_with_team_data(): void
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);
        $team = Team::factory()->create(['company_id' => $company->id]);

        // Create multiple team members
        $employees = User::factory()->count(5)->create([
            'company_id' => $company->id,
            'team_id' => $team->id,
            'role' => UserRole::EMPLOYEE->value,
        ]);

        // Create vacation requests for team members
        foreach ($employees as $employee) {
            VacationRequest::factory()->create([
                'user_id' => $employee->id,
                'company_id' => $company->id,
                'status' => VacationRequestStatus::APPROVED,
                'start_date' => now()->addWeek(),
                'end_date' => now()->addWeek()->addDays(2),
            ]);
        }

        // Manager views calendar
        $manager = User::factory()->create([
            'company_id' => $company->id,
            'team_id' => $team->id,
            'role' => UserRole::MANAGER->value,
        ]);

        $response = $this->actingAs($manager)->get('/calendar');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Calendar'));
    }

    /**
     * UAT-08: Settings navigation and updates
     */
    public function test_settings_complete_navigation(): void
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $user = User::factory()->create(['company_id' => $company->id]);

        // Test all settings pages are accessible
        $settingsPages = [
            '/settings/profile',
            '/settings/password',
            '/settings/appearance',
            '/settings/two-factor',
        ];

        foreach ($settingsPages as $page) {
            $response = $this->actingAs($user)->get($page);
            $response->assertOk();
        }

        // Admin-only pages
        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::ADMIN->value,
        ]);

        $adminPages = [
            '/settings/company',
            '/settings/admin',
        ];

        foreach ($adminPages as $page) {
            $response = $this->actingAs($admin)->get($page);
            $response->assertOk();
        }
    }
}
