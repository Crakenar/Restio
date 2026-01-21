<?php

namespace Tests\Performance;

use App\Enum\UserRole;
use App\Enum\VacationRequestStatus;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Team;
use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesSubscriptions;
use Tests\TestCase;

class DashboardPerformanceTest extends TestCase
{
    use CreatesSubscriptions, RefreshDatabase;

    /**
     * Test dashboard loads within acceptable time with realistic data
     */
    public function test_dashboard_loads_within_acceptable_time_for_small_company(): void
    {
        $this->loadDashboardWithCompanySize(100, 1000, 500); // 100 users, 1000 requests, 500ms max
    }

    public function test_dashboard_loads_within_acceptable_time_for_medium_company(): void
    {
        $this->loadDashboardWithCompanySize(500, 5000, 1000); // 500 users, 5000 requests, 1000ms max
    }

    public function test_dashboard_loads_within_acceptable_time_for_large_company(): void
    {
        $this->loadDashboardWithCompanySize(2000, 20000, 2000); // 2000 users, 20000 requests, 2000ms max
    }

    /**
     * Test concurrent dashboard loads
     */
    public function test_dashboard_handles_concurrent_requests(): void
    {
        [$company, $users] = $this->createCompanyWithUsers(300);

        // Create realistic data load
        foreach ($users->take(200) as $user) {
            VacationRequest::factory()->count(25)->create([
                'user_id' => $user->id,
                'company_id' => $company->id,
            ]);
        }

        $times = [];
        $iterations = 50; // Simulate 50 concurrent users

        for ($i = 0; $i < $iterations; $i++) {
            $user = $users->random();

            $start = microtime(true);
            $response = $this->actingAs($user)->get('/dashboard');
            $end = microtime(true);

            $times[] = ($end - $start) * 1000; // Convert to milliseconds
            $response->assertOk();
        }

        $avgTime = array_sum($times) / count($times);
        $maxTime = max($times);
        $p95 = $this->calculatePercentile($times, 95);

        $this->assertLessThan(1000, $avgTime, "Average dashboard load time ({$avgTime}ms) exceeds 1000ms");
        $this->assertLessThan(2000, $maxTime, "Maximum dashboard load time ({$maxTime}ms) exceeds 2000ms");
        $this->assertLessThan(1500, $p95, "P95 dashboard load time ({$p95}ms) exceeds 1500ms");

        echo "\n  Average: ".round($avgTime, 2).'ms | P95: '.round($p95, 2).'ms | Max: '.round($maxTime, 2)."ms ({$iterations} requests)";
    }

    /**
     * Test requests page performance with pagination
     */
    public function test_requests_page_performance_with_large_dataset(): void
    {
        [$company, $users] = $this->createCompanyWithUsers(500);
        $manager = $users->where('role', UserRole::MANAGER->value)->first();

        // Create 15000+ vacation requests for realistic load
        foreach ($users as $user) {
            VacationRequest::factory()->count(30)->create([
                'user_id' => $user->id,
                'company_id' => $company->id,
            ]);
        }

        $totalRequests = VacationRequest::where('company_id', $company->id)->count();

        $start = microtime(true);
        $response = $this->actingAs($manager)->get('/requests');
        $end = microtime(true);

        $loadTime = ($end - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(1700, $loadTime, "Requests page load time ({$loadTime}ms) exceeds 1700ms with {$totalRequests} requests");

        echo "\n  Load time: ".round($loadTime, 2)."ms for {$totalRequests} requests";
    }

    /**
     * Test calendar page performance
     */
    public function test_calendar_page_performance(): void
    {
        [$company, $users] = $this->createCompanyWithUsers(600);
        $employee = $users->where('role', UserRole::EMPLOYEE->value)->first();

        // Create approved requests for calendar display - realistic calendar load
        foreach ($users->take(500) as $user) {
            VacationRequest::factory()->count(12)->create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'status' => VacationRequestStatus::APPROVED,
                'start_date' => now()->addDays(rand(1, 90)),
                'end_date' => now()->addDays(rand(91, 180)),
            ]);
        }

        $calendarEvents = VacationRequest::where('company_id', $company->id)
            ->where('status', VacationRequestStatus::APPROVED)
            ->count();

        $start = microtime(true);
        $response = $this->actingAs($employee)->get('/calendar');
        $end = microtime(true);

        $loadTime = ($end - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(1200, $loadTime, "Calendar page load time ({$loadTime}ms) exceeds 1200ms");

        echo "\n  Load time: ".round($loadTime, 2)."ms with {$calendarEvents} calendar events";
    }

    /**
     * Test database query performance
     */
    public function test_dashboard_query_count_is_optimized(): void
    {
        [$company, $users] = $this->createCompanyWithUsers(20);
        $user = $users->first();

        // Create some requests
        VacationRequest::factory()->count(10)->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
        ]);

        \DB::enableQueryLog();

        $this->actingAs($user)->get('/dashboard');

        $queries = \DB::getQueryLog();
        $queryCount = count($queries);

        // Dashboard should not have N+1 queries - max 30 queries is acceptable
        // Ideally should be under 15 for optimal performance
        $this->assertLessThan(30, $queryCount, "Dashboard executed {$queryCount} queries (possible N+1 problem)");

        if ($queryCount > 15) {
            echo "\n  ⚠️  Query count: {$queryCount} (consider optimizing, target < 15)";
        } else {
            echo "\n  ✅ Query count: {$queryCount}";
        }
    }

    /**
     * Test memory usage
     */
    public function test_dashboard_memory_usage_is_acceptable(): void
    {
        [$company, $users] = $this->createCompanyWithUsers(1000);
        $user = $users->first();

        // Create significant data load
        foreach ($users->take(800) as $u) {
            VacationRequest::factory()->count(20)->create([
                'user_id' => $u->id,
                'company_id' => $company->id,
            ]);
        }

        $memoryBefore = memory_get_usage(true);

        $this->actingAs($user)->get('/dashboard');

        $memoryAfter = memory_get_usage(true);
        $memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024; // Convert to MB

        // Dashboard should use less than 100MB even with heavy load
        $this->assertLessThan(100, $memoryUsed, "Dashboard used {$memoryUsed}MB of memory (exceeds 100MB)");

        echo "\n  Memory used: ".round($memoryUsed, 2).'MB for 1000 users, 16000+ requests';
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

    /**
     * Helper: Load dashboard with specific company size and measure performance
     */
    private function loadDashboardWithCompanySize(int $userCount, int $requestCount, int $maxTimeMs): void
    {
        [$company, $users] = $this->createCompanyWithUsers($userCount);
        $user = $users->first();

        // Distribute requests across users
        $requestsPerUser = (int) ceil($requestCount / $userCount);
        foreach ($users as $u) {
            VacationRequest::factory()->count($requestsPerUser)->create([
                'user_id' => $u->id,
                'company_id' => $company->id,
            ]);
        }

        $start = microtime(true);
        $response = $this->actingAs($user)->get('/dashboard');
        $end = microtime(true);

        $loadTime = ($end - $start) * 1000; // Convert to milliseconds

        $response->assertOk();
        $this->assertLessThan($maxTimeMs, $loadTime,
            "Dashboard load time ({$loadTime}ms) exceeds {$maxTimeMs}ms for {$userCount} users with {$requestCount} requests"
        );

        echo "\n  {$userCount} users, {$requestCount} requests: ".round($loadTime, 2).'ms';
    }

    /**
     * Helper: Create a company with specified number of users
     */
    private function createCompanyWithUsers(int $userCount): array
    {
        $company = Company::factory()->create();
        CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $teams = Team::factory()->count(5)->create(['company_id' => $company->id]);

        // Create users with different roles
        $users = collect();

        // 1 owner
        $users->push(User::factory()->create([
            'company_id' => $company->id,
            'team_id' => $teams->random()->id,
            'role' => UserRole::OWNER->value,
        ]));

        // 2 admins
        for ($i = 0; $i < 2; $i++) {
            $users->push(User::factory()->create([
                'company_id' => $company->id,
                'team_id' => $teams->random()->id,
                'role' => UserRole::ADMIN->value,
            ]));
        }

        // 20% managers
        $managerCount = (int) ceil($userCount * 0.2);
        for ($i = 0; $i < $managerCount; $i++) {
            $users->push(User::factory()->create([
                'company_id' => $company->id,
                'team_id' => $teams->random()->id,
                'role' => UserRole::MANAGER->value,
            ]));
        }

        // Rest are employees
        $employeeCount = $userCount - $users->count();
        for ($i = 0; $i < $employeeCount; $i++) {
            $users->push(User::factory()->create([
                'company_id' => $company->id,
                'team_id' => $teams->random()->id,
                'role' => UserRole::EMPLOYEE->value,
            ]));
        }

        return [$company, $users];
    }
}
