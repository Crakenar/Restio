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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarControllerTest extends TestCase
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
     * Test that authenticated user can access calendar page.
     */
    public function test_authenticated_user_can_access_calendar(): void
    {
        $company = $this->createCompanyWithSubscription();
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $response = $this->get('/calendar');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('VacationCalendarPage')
            ->has('requests')
            ->has('userName')
            ->has('userRole')
        );
    }

    /**
     * Test that unauthenticated user cannot access calendar page.
     */
    public function test_unauthenticated_user_cannot_access_calendar(): void
    {
        $response = $this->get('/calendar');

        $response->assertRedirect('/login');
    }

    /**
     * Test that calendar shows user's own vacation requests.
     */
    public function test_calendar_shows_users_own_requests(): void
    {
        $company = $this->createCompanyWithSubscription();
        $user = User::factory()->create(['company_id' => $company->id]);
        $otherUser = User::factory()->create(['company_id' => $company->id]);

        // Create requests for the user
        $userRequest = VacationRequest::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'status' => VacationRequestStatus::APPROVED,
            'type' => VacationRequestType::VACATION,
        ]);

        // Create request for another user in same company
        VacationRequest::factory()->create([
            'user_id' => $otherUser->id,
            'company_id' => $company->id,
            'status' => VacationRequestStatus::APPROVED,
            'type' => VacationRequestType::VACATION,
        ]);

        $this->actingAs($user);

        $response = $this->get('/calendar');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('VacationCalendarPage')
            ->where('requests', fn ($requests) => count($requests) === 1 && $requests[0]['id'] === $userRequest->id)
            ->where('userName', $user->name)
            ->where('userRole', $user->role)
        );
    }

    /**
     * Test that calendar does not show requests from other companies.
     */
    public function test_calendar_does_not_show_requests_from_other_companies(): void
    {
        $company1 = $this->createCompanyWithSubscription();
        $company2 = $this->createCompanyWithSubscription();

        $user = User::factory()->create(['company_id' => $company1->id]);
        $otherCompanyUser = User::factory()->create(['company_id' => $company2->id]);

        // Create request for user in company 1
        VacationRequest::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company1->id,
            'status' => VacationRequestStatus::APPROVED,
        ]);

        // Create request for user in company 2
        VacationRequest::factory()->create([
            'user_id' => $otherCompanyUser->id,
            'company_id' => $company2->id,
            'status' => VacationRequestStatus::APPROVED,
        ]);

        $this->actingAs($user);

        $response = $this->get('/calendar');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('VacationCalendarPage')
            ->where('requests', fn ($requests) => count($requests) === 1)
        );
    }

    /**
     * Test that calendar request data includes all necessary fields.
     */
    public function test_calendar_request_data_includes_necessary_fields(): void
    {
        $company = $this->createCompanyWithSubscription();
        $user = User::factory()->create(['company_id' => $company->id]);

        VacationRequest::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'status' => VacationRequestStatus::PENDING,
            'type' => VacationRequestType::VACATION,
            'reason' => 'Summer vacation',
        ]);

        $this->actingAs($user);

        $response = $this->get('/calendar');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('VacationCalendarPage')
            ->where('requests.0', fn ($request) => isset($request['id'])
                && isset($request['startDate'])
                && isset($request['endDate'])
                && isset($request['type'])
                && isset($request['status'])
                && isset($request['employeeName'])
                && isset($request['days'])
            )
        );
    }
}
