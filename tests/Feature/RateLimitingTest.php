<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_rate_limiting_applies_to_all_routes(): void
    {
        $user = User::factory()->create();

        // Make 61 requests (above the 60/minute global limit)
        for ($i = 0; $i < 61; $i++) {
            $response = $this->actingAs($user)->get('/dashboard');

            if ($i < 60) {
                $response->assertSuccessful();
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    public function test_sensitive_actions_have_stricter_rate_limiting(): void
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

        $vacationRequest = VacationRequest::factory()->create([
            'company_id' => $company->id,
            'user_id' => $employee->id,
            'status' => 'pending',
        ]);

        // Make 21 approval attempts (above the 20/minute sensitive limit)
        for ($i = 0; $i < 21; $i++) {
            $response = $this->actingAs($admin)->post(route('vacation-requests.approve', $vacationRequest));

            if ($i < 20) {
                // Will fail after first due to status change, but should not rate limit
                $this->assertTrue(in_array($response->status(), [200, 302, 422]));
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    public function test_admin_actions_have_rate_limiting(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin',
        ]);

        // Make 31 employee creation attempts (above the 30/minute admin limit)
        for ($i = 0; $i < 31; $i++) {
            $response = $this->actingAs($admin)->post(route('employees.store'), [
                'name' => 'Test Employee '.$i,
                'email' => 'employee'.$i.'@test.com',
                'role' => 'employee',
            ]);

            if ($i < 30) {
                $this->assertTrue(in_array($response->status(), [200, 302, 422]));
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    public function test_billing_actions_have_strict_rate_limiting(): void
    {
        $company = Company::factory()->create();
        $owner = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'owner',
        ]);

        // Make 11 subscription change attempts (above the 10/minute billing limit)
        for ($i = 0; $i < 11; $i++) {
            $response = $this->actingAs($owner)->post(route('subscription.change'), [
                'subscription_id' => 1,
            ]);

            if ($i < 10) {
                // Will fail validation but should not rate limit
                $this->assertTrue(in_array($response->status(), [200, 302, 422]));
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    public function test_import_actions_have_hourly_rate_limiting(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin',
        ]);

        // Make 6 import attempts (above the 5/hour import limit)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->actingAs($admin)->post(route('employees.import'), [
                'file' => null, // Will fail validation
            ]);

            if ($i < 5) {
                // Will fail validation but should not rate limit
                $this->assertTrue(in_array($response->status(), [302, 422]));
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    public function test_password_update_has_strict_rate_limiting(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('old-password'),
        ]);

        // Make 7 password update attempts (above the 6/minute limit)
        for ($i = 0; $i < 7; $i++) {
            $response = $this->actingAs($user)->put(route('user-password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

            if ($i < 6) {
                // Will fail validation but should not rate limit
                $this->assertTrue(in_array($response->status(), [302, 422]));
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }
}
