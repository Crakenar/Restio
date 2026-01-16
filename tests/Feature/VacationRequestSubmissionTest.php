<?php

namespace Tests\Feature;

use App\Enum\SubscriptionStatus;
use App\Enum\VacationRequestStatus;
use App\Enum\VacationRequestType;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\CompanySubscription;
use App\Models\Subscription;
use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacationRequestSubmissionTest extends TestCase
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
     * Test that authenticated user can submit a vacation request.
     */
    public function test_user_can_submit_vacation_request(): void
    {
        $company = $this->createCompanyWithSubscription();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $response = $this->post('/vacation-requests', [
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'type' => VacationRequestType::VACATION->value,
            'reason' => 'Summer vacation',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Your time off request has been submitted successfully!');

        $this->assertDatabaseHas('vacation_requests', [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'type' => VacationRequestType::VACATION->value,
            'status' => VacationRequestStatus::PENDING->value,
            'reason' => 'Summer vacation',
        ]);
    }

    /**
     * Test that start date must be today or future.
     */
    public function test_start_date_must_be_today_or_future(): void
    {
        $company = $this->createCompanyWithSubscription();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $response = $this->post('/vacation-requests', [
            'start_date' => now()->subDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'type' => VacationRequestType::VACATION->value,
        ]);

        $response->assertSessionHasErrors(['start_date']);
    }

    /**
     * Test that end date must be on or after start date.
     */
    public function test_end_date_must_be_after_or_equal_to_start_date(): void
    {
        $company = $this->createCompanyWithSubscription();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $response = $this->post('/vacation-requests', [
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'type' => VacationRequestType::VACATION->value,
        ]);

        $response->assertSessionHasErrors(['end_date']);
    }

    /**
     * Test that overlapping requests are rejected.
     */
    public function test_cannot_create_overlapping_requests(): void
    {
        $company = $this->createCompanyWithSubscription();
        $user = User::factory()->create(['company_id' => $company->id]);

        // Create existing request
        VacationRequest::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
            'status' => VacationRequestStatus::PENDING,
        ]);

        $this->actingAs($user);

        // Try to create overlapping request
        $response = $this->post('/vacation-requests', [
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(12)->format('Y-m-d'),
            'type' => VacationRequestType::VACATION->value,
        ]);

        $response->assertSessionHasErrors(['start_date']);
    }

    /**
     * Test that annual days limit is enforced for vacation type.
     */
    public function test_annual_days_limit_is_enforced(): void
    {
        $company = $this->createCompanyWithSubscription();
        CompanySetting::factory()->create([
            'company_id' => $company->id,
            'annual_days' => 10,
        ]);
        $user = User::factory()->create(['company_id' => $company->id]);

        // Create approved request using 8 days
        VacationRequest::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'start_date' => now()->startOfYear()->addDays(10),
            'end_date' => now()->startOfYear()->addDays(17),
            'type' => VacationRequestType::VACATION,
            'status' => VacationRequestStatus::APPROVED,
        ]);

        $this->actingAs($user);

        // Try to request 5 more days (total would be 13, exceeds 10)
        $response = $this->post('/vacation-requests', [
            'start_date' => now()->addDays(20)->format('Y-m-d'),
            'end_date' => now()->addDays(24)->format('Y-m-d'),
            'type' => VacationRequestType::VACATION->value,
        ]);

        $response->assertSessionHasErrors(['end_date']);
    }

    /**
     * Test that annual days limit does not apply to sick leave.
     */
    public function test_annual_days_limit_does_not_apply_to_sick_leave(): void
    {
        $company = $this->createCompanyWithSubscription();
        CompanySetting::factory()->create([
            'company_id' => $company->id,
            'annual_days' => 10,
        ]);
        $user = User::factory()->create(['company_id' => $company->id]);

        // Create approved vacation request using all 10 days
        VacationRequest::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'start_date' => now()->startOfYear()->addDays(10),
            'end_date' => now()->startOfYear()->addDays(19),
            'type' => VacationRequestType::VACATION,
            'status' => VacationRequestStatus::APPROVED,
        ]);

        $this->actingAs($user);

        // Should be able to request sick leave even though vacation allowance is used
        $response = $this->post('/vacation-requests', [
            'start_date' => now()->addDays(25)->format('Y-m-d'),
            'end_date' => now()->addDays(30)->format('Y-m-d'),
            'type' => VacationRequestType::SICK->value,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test that unauthenticated users cannot submit requests.
     */
    public function test_unauthenticated_users_cannot_submit_requests(): void
    {
        $response = $this->post('/vacation-requests', [
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'type' => VacationRequestType::VACATION->value,
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test that reason is optional and can be up to 500 characters.
     */
    public function test_reason_is_optional_and_has_max_length(): void
    {
        $company = $this->createCompanyWithSubscription();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        // Test without reason
        $response = $this->post('/vacation-requests', [
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'type' => VacationRequestType::VACATION->value,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Test with reason exceeding 500 characters
        $response = $this->post('/vacation-requests', [
            'start_date' => now()->addDays(15)->format('Y-m-d'),
            'end_date' => now()->addDays(20)->format('Y-m-d'),
            'type' => VacationRequestType::VACATION->value,
            'reason' => str_repeat('a', 501),
        ]);

        $response->assertSessionHasErrors(['reason']);
    }
}
