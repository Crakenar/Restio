<?php

namespace Tests\Feature;

use App\Enum\SubscriptionStatus;
use App\Enum\VacationRequestStatus;
use App\Enum\VacationRequestType;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Subscription;
use App\Models\User;
use App\Models\VacationRequest;
use App\Notifications\VacationRequestApproved;
use App\Notifications\VacationRequestRejected;
use App\Notifications\VacationRequestSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VacationRequestNotificationTest extends TestCase
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
     * Test that managers, admins, and owners are notified when a request is submitted.
     */
    public function test_managers_admins_and_owners_are_notified_when_request_is_submitted(): void
    {
        Notification::fake();

        $company = $this->createCompanyWithSubscription();

        // Create users with different roles
        $employee = User::factory()->create(['company_id' => $company->id, 'role' => 'employee']);
        $manager = User::factory()->create(['company_id' => $company->id, 'role' => 'manager']);
        $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
        $owner = User::factory()->create(['company_id' => $company->id, 'role' => 'owner']);

        // Another employee who should NOT receive notification
        $anotherEmployee = User::factory()->create(['company_id' => $company->id, 'role' => 'employee']);

        $this->actingAs($employee);

        $this->post('/vacation-requests', [
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'type' => VacationRequestType::VACATION->value,
            'reason' => 'Summer vacation',
        ]);

        // Assert notifications were sent to manager, admin, and owner
        Notification::assertSentTo([$manager, $admin, $owner], VacationRequestSubmitted::class);

        // Assert notification was NOT sent to other employees
        Notification::assertNotSentTo([$employee, $anotherEmployee], VacationRequestSubmitted::class);
    }

    /**
     * Test that only users in the same company receive notifications.
     */
    public function test_only_users_in_same_company_receive_notifications(): void
    {
        Notification::fake();

        $company1 = $this->createCompanyWithSubscription();
        $company2 = $this->createCompanyWithSubscription();

        $employee = User::factory()->create(['company_id' => $company1->id, 'role' => 'employee']);
        $managerInCompany1 = User::factory()->create(['company_id' => $company1->id, 'role' => 'manager']);
        $managerInCompany2 = User::factory()->create(['company_id' => $company2->id, 'role' => 'manager']);

        $this->actingAs($employee);

        $this->post('/vacation-requests', [
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'type' => VacationRequestType::VACATION->value,
        ]);

        // Assert notification was sent to manager in same company
        Notification::assertSentTo($managerInCompany1, VacationRequestSubmitted::class);

        // Assert notification was NOT sent to manager in different company
        Notification::assertNotSentTo($managerInCompany2, VacationRequestSubmitted::class);
    }

    /**
     * Test that employee is notified when their request is approved.
     */
    public function test_employee_is_notified_when_request_is_approved(): void
    {
        Notification::fake();

        $company = $this->createCompanyWithSubscription();
        $employee = User::factory()->create(['company_id' => $company->id, 'role' => 'employee']);
        $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

        $vacationRequest = VacationRequest::factory()->create([
            'user_id' => $employee->id,
            'company_id' => $company->id,
            'status' => VacationRequestStatus::PENDING,
        ]);

        $this->actingAs($admin);

        $this->post("/vacation-requests/{$vacationRequest->id}/approve");

        // Assert notification was sent to employee
        Notification::assertSentTo($employee, VacationRequestApproved::class);
    }

    /**
     * Test that employee is notified when their request is rejected.
     */
    public function test_employee_is_notified_when_request_is_rejected(): void
    {
        Notification::fake();

        $company = $this->createCompanyWithSubscription();
        $employee = User::factory()->create(['company_id' => $company->id, 'role' => 'employee']);
        $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

        $vacationRequest = VacationRequest::factory()->create([
            'user_id' => $employee->id,
            'company_id' => $company->id,
            'status' => VacationRequestStatus::PENDING,
        ]);

        $this->actingAs($admin);

        $this->post("/vacation-requests/{$vacationRequest->id}/reject", [
            'rejection_reason' => 'Not enough staff coverage',
        ]);

        // Assert notification was sent to employee
        Notification::assertSentTo($employee, VacationRequestRejected::class);
    }

    /**
     * Test that notification contains correct vacation request data.
     */
    public function test_notification_contains_correct_vacation_request_data(): void
    {
        Notification::fake();

        $company = $this->createCompanyWithSubscription();
        $employee = User::factory()->create(['company_id' => $company->id, 'role' => 'employee']);
        $manager = User::factory()->create(['company_id' => $company->id, 'role' => 'manager']);

        $this->actingAs($employee);

        $this->post('/vacation-requests', [
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'type' => VacationRequestType::VACATION->value,
            'reason' => 'Summer vacation',
        ]);

        Notification::assertSentTo(
            $manager,
            function (VacationRequestSubmitted $notification) use ($employee) {
                $vacationRequest = $notification->vacationRequest;

                return $vacationRequest->user_id === $employee->id
                    && $vacationRequest->type === VacationRequestType::VACATION
                    && $vacationRequest->reason === 'Summer vacation';
            }
        );
    }
}
