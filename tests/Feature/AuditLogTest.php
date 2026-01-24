<?php

namespace Tests\Feature;

use App\Enum\SubscriptionStatus;
use App\Enum\VacationRequestStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test subscription
        Subscription::create([
            'name' => 'Test Plan',
            'slug' => 'test',
            'price' => 10,
            'currency' => 'usd',
            'interval' => 'month',
        ]);
    }

    public function test_failed_login_is_logged(): void
    {
        // Attempt to login with invalid credentials
        $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert that a failed login was logged
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'login.failed',
        ]);

        $log = AuditLog::where('event', 'login.failed')->first();
        $this->assertNotNull($log);
        $this->assertEquals('nonexistent@example.com', $log->metadata['email']);
    }

    public function test_successful_login_is_logged(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        // Login with valid credentials
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Assert that a successful login was logged
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'login.success',
            'user_id' => $user->id,
            'company_id' => $user->company_id,
        ]);
    }

    public function test_vacation_request_approval_is_logged(): void
    {
        $manager = User::factory()->manager()->create();
        $employee = User::factory()->create(['company_id' => $manager->company_id]);
        $team = Team::factory()->create(['company_id' => $manager->company_id]);
        $team->users()->attach([$manager->id, $employee->id]);

        $request = VacationRequest::factory()->create([
            'user_id' => $employee->id,
            'company_id' => $manager->company_id,
            'status' => VacationRequestStatus::PENDING,
        ]);

        $this->actingAs($manager)
            ->post(route('vacation-requests.approve', $request));

        // Assert that the approval was logged
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'request.approved',
            'auditable_type' => VacationRequest::class,
            'auditable_id' => $request->id,
            'user_id' => $manager->id,
            'company_id' => $manager->company_id,
        ]);

        $log = AuditLog::where('event', 'request.approved')->first();
        $this->assertNotNull($log->metadata['requester_name']);
        $this->assertNotNull($log->metadata['start_date']);
        $this->assertNotNull($log->metadata['end_date']);
    }

    public function test_vacation_request_rejection_is_logged(): void
    {
        $manager = User::factory()->manager()->create();
        $employee = User::factory()->create(['company_id' => $manager->company_id]);
        $team = Team::factory()->create(['company_id' => $manager->company_id]);
        $team->users()->attach([$manager->id, $employee->id]);

        $request = VacationRequest::factory()->create([
            'user_id' => $employee->id,
            'company_id' => $manager->company_id,
            'status' => VacationRequestStatus::PENDING,
        ]);

        $this->actingAs($manager)
            ->post(route('vacation-requests.reject', $request), [
                'rejection_reason' => 'Not enough coverage',
            ]);

        // Assert that the rejection was logged
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'request.rejected',
            'auditable_type' => VacationRequest::class,
            'auditable_id' => $request->id,
            'user_id' => $manager->id,
            'company_id' => $manager->company_id,
        ]);

        $log = AuditLog::where('event', 'request.rejected')->first();
        $this->assertEquals('Not enough coverage', $log->metadata['rejection_reason']);
    }

    public function test_employee_creation_is_logged(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('employees.store'), [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'password123',
                'role' => 'employee',
            ]);

        // Assert that the user creation was logged
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'auditable_type' => User::class,
            'user_id' => $admin->id,
            'company_id' => $admin->company_id,
        ]);

        $log = AuditLog::where('event', 'created')
            ->where('auditable_type', User::class)
            ->first();

        $this->assertEquals('John Doe', $log->metadata['name']);
        $this->assertEquals('john@example.com', $log->metadata['email']);
        $this->assertEquals('employee', $log->metadata['role']);
    }

    public function test_company_settings_change_is_logged(): void
    {
        $owner = User::factory()->owner()->create();
        $company = $owner->company;

        // Create initial settings
        CompanySetting::create([
            'company_id' => $company->id,
            'annual_days' => 20,
            'approval_required' => true,
        ]);

        $this->actingAs($owner)
            ->put(route('settings.company.update'), [
                'name' => 'Updated Company Name',
                'timezone' => 'America/New_York',
                'annual_days' => 25,
                'approval_required' => false,
            ]);

        // Assert that settings change was logged
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'settings.changed',
            'auditable_type' => CompanySetting::class,
            'user_id' => $owner->id,
            'company_id' => $owner->company_id,
        ]);

        $log = AuditLog::where('event', 'settings.changed')->first();
        $this->assertEquals(20, $log->metadata['old_annual_days']);
        $this->assertEquals(25, $log->metadata['new_annual_days']);
        $this->assertEquals(true, $log->metadata['old_approval_required']);
        $this->assertEquals(false, $log->metadata['new_approval_required']);
    }

    public function test_subscription_cancellation_is_logged(): void
    {
        $owner = User::factory()->owner()->create();
        $company = $owner->company;

        // Create an active subscription
        $subscription = Subscription::first();
        $company->subscriptions()->create([
            'subscription_id' => $subscription->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->actingAs($owner)
            ->post(route('subscription.cancel'));

        // Assert that subscription cancellation was logged
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'subscription.cancelled',
            'user_id' => $owner->id,
            'company_id' => $owner->company_id,
        ]);

        $log = AuditLog::where('event', 'subscription.cancelled')->first();
        $this->assertNotNull($log->metadata['plan']);
    }

    public function test_audit_log_records_ip_and_user_agent(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        // Login with valid credentials
        $this->withHeaders([
            'User-Agent' => 'TestBrowser/1.0',
        ])->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $log = AuditLog::where('event', 'login.success')->first();
        $this->assertNotNull($log->ip_address);
        $this->assertEquals('TestBrowser/1.0', $log->user_agent);
    }
}
