<?php

namespace Tests\Feature;

use App\Enum\SubscriptionStatus;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Department;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $company = Company::factory()->create();
        $department = Department::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
        ]);

        $subscription = Subscription::factory()->create();
        CompanySubscription::create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
    }
}
