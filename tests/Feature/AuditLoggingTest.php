<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_vacation_request_creation_is_audited(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $vacationRequest = VacationRequest::factory()->create([
            'company_id' => $company->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'auditable_type' => VacationRequest::class,
            'auditable_id' => $vacationRequest->id,
            'user_id' => $user->id,
            'company_id' => $company->id,
        ]);
    }

    public function test_vacation_request_update_is_audited(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $vacationRequest = VacationRequest::factory()->create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        // Clear created audit log
        AuditLog::query()->delete();

        $vacationRequest->update(['status' => 'approved']);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'updated',
            'auditable_type' => VacationRequest::class,
            'auditable_id' => $vacationRequest->id,
            'user_id' => $user->id,
        ]);

        $log = AuditLog::where('auditable_id', $vacationRequest->id)
            ->where('event', 'updated')
            ->first();

        $this->assertEquals('pending', $log->old_values['status']);
        $this->assertEquals('approved', $log->new_values['status']);
    }

    public function test_vacation_request_deletion_is_audited(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $vacationRequest = VacationRequest::factory()->create([
            'company_id' => $company->id,
            'user_id' => $user->id,
        ]);

        $vacationRequestId = $vacationRequest->id;

        // Clear created audit log
        AuditLog::query()->delete();

        $vacationRequest->delete();

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'deleted',
            'auditable_type' => VacationRequest::class,
            'auditable_id' => $vacationRequestId,
            'user_id' => $user->id,
        ]);
    }

    public function test_company_settings_update_is_audited(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $settings = CompanySetting::factory()->create([
            'company_id' => $company->id,
            'annual_days' => 20,
        ]);

        // Clear created audit log
        AuditLog::query()->delete();

        $settings->update(['annual_days' => 25]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'updated',
            'auditable_type' => CompanySetting::class,
            'auditable_id' => $settings->id,
            'user_id' => $admin->id,
        ]);

        $log = AuditLog::where('auditable_id', $settings->id)
            ->where('event', 'updated')
            ->first();

        $this->assertEquals(20, $log->old_values['annual_days']);
        $this->assertEquals(25, $log->new_values['annual_days']);
    }

    public function test_user_role_change_is_audited(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin',
        ]);
        $employee = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'employee',
        ]);

        $this->actingAs($admin);

        // Clear created audit logs
        AuditLog::query()->delete();

        $employee->update(['role' => 'manager']);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'updated',
            'auditable_type' => User::class,
            'auditable_id' => $employee->id,
            'user_id' => $admin->id,
        ]);

        $log = AuditLog::where('auditable_id', $employee->id)
            ->where('event', 'updated')
            ->first();

        $this->assertEquals('employee', $log->old_values['role']);
        $this->assertEquals('manager', $log->new_values['role']);
    }

    public function test_audit_log_includes_metadata(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        VacationRequest::factory()->create([
            'company_id' => $company->id,
            'user_id' => $user->id,
        ]);

        $log = AuditLog::where('event', 'created')->first();

        $this->assertNotNull($log->metadata);
        $this->assertArrayHasKey('ip_address', $log->metadata);
        $this->assertArrayHasKey('user_agent', $log->metadata);
    }

    public function test_audit_log_relationships_work(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $vacationRequest = VacationRequest::factory()->create([
            'company_id' => $company->id,
            'user_id' => $user->id,
        ]);

        $log = AuditLog::where('auditable_id', $vacationRequest->id)->first();

        $this->assertInstanceOf(VacationRequest::class, $log->auditable);
        $this->assertInstanceOf(User::class, $log->user);
        $this->assertInstanceOf(Company::class, $log->company);
        $this->assertEquals($user->id, $log->user->id);
        $this->assertEquals($company->id, $log->company->id);
    }

    public function test_model_has_audit_logs_relationship(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $vacationRequest = VacationRequest::factory()->create([
            'company_id' => $company->id,
            'user_id' => $user->id,
        ]);

        $vacationRequest->update(['status' => 'approved']);
        $vacationRequest->update(['status' => 'rejected']);

        $this->assertCount(3, $vacationRequest->auditLogs); // created, updated x2
    }
}
