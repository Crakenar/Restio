<?php

namespace Tests\Performance;

use App\Enum\UserRole;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Team;
use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesSubscriptions;
use Tests\TestCase;

class ApiEndpointPerformanceTest extends TestCase
{
    use CreatesSubscriptions, RefreshDatabase;

    /**
     * Test vacation request submission performance
     */
    public function test_vacation_request_submission_is_fast(): void
    {
        [$company, $user] = $this->createUserWithCompany();

        // Create existing data for realistic scenario
        VacationRequest::factory()->count(2000)->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
        ]);

        $times = [];
        $iterations = 100; // Test 100 submissions

        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);

            $response = $this->actingAs($user)->post('/vacation-requests', [
                'start_date' => now()->addDays($i + 1)->toDateString(),
                'end_date' => now()->addDays($i + 5)->toDateString(),
                'type' => 'vacation',
                'reason' => 'Performance test request '.$i,
            ]);

            $end = microtime(true);
            $times[] = ($end - $start) * 1000;

            $response->assertRedirect();
        }

        $avgTime = array_sum($times) / count($times);
        $p95 = $this->calculatePercentile($times, 95);
        $p99 = $this->calculatePercentile($times, 99);

        $this->assertLessThan(500, $avgTime, "Average submission time ({$avgTime}ms) exceeds 500ms");
        $this->assertLessThan(750, $p95, "P95 submission time ({$p95}ms) exceeds 750ms");
        $this->assertLessThan(1000, $p99, "P99 submission time ({$p99}ms) exceeds 1000ms");

        echo "\n  Average: ".round($avgTime, 2).'ms | P95: '.round($p95, 2).'ms | P99: '.round($p99, 2)."ms ({$iterations} submissions)";
    }

    /**
     * Test vacation request approval performance
     */
    public function test_vacation_request_approval_is_fast(): void
    {
        [$company, $manager, $employees] = $this->createManagerWithTeam(300);

        // Create pending requests with realistic data volume
        $requests = [];
        foreach ($employees as $employee) {
            // Create some existing historical data
            VacationRequest::factory()->count(30)->create([
                'user_id' => $employee->id,
                'company_id' => $company->id,
            ]);

            // Create pending request to approve
            $requests[] = VacationRequest::factory()->create([
                'user_id' => $employee->id,
                'company_id' => $company->id,
                'status' => 'pending',
            ]);
        }

        $times = [];

        foreach ($requests as $request) {
            $start = microtime(true);

            $response = $this->actingAs($manager)->post("/vacation-requests/{$request->id}/approve");

            $end = microtime(true);
            $times[] = ($end - $start) * 1000;

            $response->assertRedirect();
        }

        $avgTime = array_sum($times) / count($times);
        $maxTime = max($times);
        $p95 = $this->calculatePercentile($times, 95);

        $this->assertLessThan(500, $avgTime, "Average approval time ({$avgTime}ms) exceeds 500ms");
        $this->assertLessThan(1000, $maxTime, "Maximum approval time ({$maxTime}ms) exceeds 1000ms");

        echo "\n  Average: ".round($avgTime, 2).'ms | P95: '.round($p95, 2).'ms | Max: '.round($maxTime, 2).'ms ('.count($requests).' approvals)';
    }

    /**
     * Test team management operations performance
     */
    public function test_team_creation_is_fast(): void
    {
        [$company, $admin] = $this->createAdminWithCompany();

        $times = [];
        $iterations = 50;

        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);

            $response = $this->actingAs($admin)->post('/team-management', [
                'name' => 'Performance Team '.$i,
            ]);

            $end = microtime(true);
            $times[] = ($end - $start) * 1000;

            $response->assertRedirect();
        }

        $avgTime = array_sum($times) / count($times);

        $this->assertLessThan(200, $avgTime, "Average team creation time ({$avgTime}ms) exceeds 200ms");

        echo "\n  Average: ".round($avgTime, 2)."ms for {$iterations} team creations";
    }

    /**
     * Test user assignment to team performance
     */
    public function test_bulk_user_assignment_is_fast(): void
    {
        [$company, $admin] = $this->createAdminWithCompany();

        // Create multiple teams for realistic scenario
        $teams = Team::factory()->count(20)->create(['company_id' => $company->id]);
        $team = $teams->first();

        // Create 500 users (realistic large company size)
        $users = User::factory()->count(500)->create([
            'company_id' => $company->id,
        ]);

        $userIds = $users->pluck('id')->toArray();

        $start = microtime(true);

        $response = $this->actingAs($admin)->post("/team-management/{$team->id}/assign-users", [
            'user_ids' => $userIds,
        ]);

        $end = microtime(true);
        $loadTime = ($end - $start) * 1000;

        $response->assertRedirect();
        $this->assertLessThan(2000, $loadTime, "Bulk assignment of 500 users took {$loadTime}ms (exceeds 2000ms)");

        echo "\n  Assigned 500 users in ".round($loadTime, 2).'ms';
    }

    /**
     * Test settings update performance
     */
    public function test_company_settings_update_is_fast(): void
    {
        [$company, $owner] = $this->createOwnerWithCompany();

        $times = [];
        $iterations = 25;

        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);

            $response = $this->actingAs($owner)->post('/settings/company', [
                'company_name' => 'Updated Company Name '.$i,
                'annual_days' => 20 + $i,
            ]);

            $end = microtime(true);
            $times[] = ($end - $start) * 1000;

            $response->assertRedirect();
        }

        $avgTime = array_sum($times) / count($times);

        $this->assertLessThan(200, $avgTime, "Average settings update time ({$avgTime}ms) exceeds 200ms");

        echo "\n  Average: ".round($avgTime, 2)."ms for {$iterations} settings updates";
    }

    /**
     * Test notification dispatch performance
     */
    public function test_notification_dispatch_does_not_slow_requests(): void
    {
        [$company, $manager, $employees] = $this->createManagerWithTeam(20);
        $employee = $employees->first();

        // Submit request which triggers notifications to manager
        $start = microtime(true);

        $response = $this->actingAs($employee)->post('/vacation-requests', [
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'type' => 'vacation',
            'reason' => 'Notification performance test',
        ]);

        $end = microtime(true);
        $loadTime = ($end - $start) * 1000;

        $response->assertRedirect();

        // Even with notifications, request should complete quickly (notifications should be queued)
        $this->assertLessThan(400, $loadTime, "Request with notification took {$loadTime}ms (exceeds 400ms)");

        echo "\n  Request with notifications: ".round($loadTime, 2).'ms';
    }

    /**
     * Helper: Create user with company
     */
    private function createUserWithCompany(): array
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $team = Team::factory()->create(['company_id' => $company->id]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'team_id' => $team->id,
            'role' => UserRole::EMPLOYEE->value,
        ]);

        return [$company, $user];
    }

    /**
     * Helper: Create admin with company
     */
    private function createAdminWithCompany(): array
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::ADMIN->value,
        ]);

        return [$company, $admin];
    }

    /**
     * Helper: Create owner with company
     */
    private function createOwnerWithCompany(): array
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $owner = User::factory()->create([
            'company_id' => $company->id,
            'role' => UserRole::OWNER->value,
        ]);

        return [$company, $owner];
    }

    /**
     * Helper: Create manager with team
     */
    private function createManagerWithTeam(int $employeeCount): array
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $team = Team::factory()->create(['company_id' => $company->id]);

        $manager = User::factory()->create([
            'company_id' => $company->id,
            'team_id' => $team->id,
            'role' => UserRole::MANAGER->value,
        ]);

        $employees = User::factory()->count($employeeCount)->create([
            'company_id' => $company->id,
            'team_id' => $team->id,
            'role' => UserRole::EMPLOYEE->value,
        ]);

        return [$company, $manager, $employees];
    }

    /**
     * Calculate percentile from array of values
     */
    private function calculatePercentile(array $values, int $percentile): float
    {
        sort($values);
        $index = (int) ceil(($percentile / 100) * count($values)) - 1;

        return $values[$index] ?? 0;
    }
}
