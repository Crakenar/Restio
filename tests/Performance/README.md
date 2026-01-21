# Performance Testing Suite

This directory contains integrated performance tests that measure the application's speed, efficiency, and scalability.

## Quick Start

```bash
# Run all performance tests with nice output
php artisan test:performance

# Run specific test file
php artisan test tests/Performance/DashboardPerformanceTest.php

# Run specific test method
php artisan test:performance --filter=dashboard_handles_concurrent_requests

# Run without confirmation prompt
php artisan test --testsuite=Performance
```

## Test Files

### DashboardPerformanceTest.php

Tests dashboard and page load performance under various conditions.

**Tests:**
- `test_dashboard_loads_within_acceptable_time_for_small_company` - 10 users, 50 requests (< 200ms)
- `test_dashboard_loads_within_acceptable_time_for_medium_company` - 50 users, 250 requests (< 500ms)
- `test_dashboard_loads_within_acceptable_time_for_large_company` - 100 users, 500 requests (< 1000ms)
- `test_dashboard_handles_concurrent_requests` - 10 concurrent requests
- `test_requests_page_performance_with_large_dataset` - 500+ vacation requests (< 800ms)
- `test_calendar_page_performance` - 150 calendar events (< 600ms)
- `test_dashboard_query_count_is_optimized` - Query count < 25 (target < 15)
- `test_dashboard_memory_usage_is_acceptable` - Memory usage < 50MB

### ApiEndpointPerformanceTest.php

Tests API endpoint response times and operation performance.

**Tests:**
- `test_vacation_request_submission_is_fast` - 20 submissions, avg < 300ms
- `test_vacation_request_approval_is_fast` - 10 approvals, avg < 250ms
- `test_team_creation_is_fast` - 15 team creations, avg < 200ms
- `test_bulk_user_assignment_is_fast` - 50 users assigned < 500ms
- `test_company_settings_update_is_fast` - 10 updates, avg < 200ms
- `test_notification_dispatch_does_not_slow_requests` - With notifications < 400ms

## Performance Targets

| Metric | Target | Critical |
|--------|--------|----------|
| Dashboard (Small) | < 200ms | < 500ms |
| Dashboard (Medium) | < 500ms | < 800ms |
| Dashboard (Large) | < 1000ms | < 1500ms |
| API Requests | < 300ms | < 500ms |
| Query Count | < 15 | < 25 |
| Memory Usage | < 50MB | < 100MB |

## Understanding Results

### Good Performance ✅
```
10 users, 50 requests: 185.23ms
Average: 156.34ms | Max: 245.12ms
✅ Query count: 12
Memory used: 8.5MB
```

### Needs Optimization ⚠️
```
100 users, 500 requests: 1450.12ms
Average: 780.45ms | Max: 1200.33ms
⚠️  Query count: 20 (consider optimizing, target < 15)
Memory used: 65MB
```

### Critical Issues 🚨
```
Dashboard executed 50 queries (possible N+1 problem)
Memory used: 120MB
Average: 2500ms | Max: 5000ms
```

## Creating Custom Performance Tests

```bash
# Create new test file
php artisan make:test Performance/YourFeaturePerformanceTest
```

```php
<?php

namespace Tests\Performance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesSubscriptions;
use Tests\TestCase;

class YourFeaturePerformanceTest extends TestCase
{
    use RefreshDatabase, CreatesSubscriptions;

    public function test_your_feature_is_fast(): void
    {
        // Setup test data
        [$company, $user] = $this->createTestUser();

        // Measure performance
        $start = microtime(true);

        $response = $this->actingAs($user)->get('/your-endpoint');

        $end = microtime(true);
        $loadTime = ($end - $start) * 1000; // Convert to milliseconds

        // Assert performance
        $response->assertOk();
        $this->assertLessThan(300, $loadTime,
            "Endpoint took {$loadTime}ms (exceeds 300ms)"
        );

        // Optional: Display result
        echo "\n  Load time: ".round($loadTime, 2)."ms";
    }

    private function createTestUser(): array
    {
        $company = \App\Models\Company::factory()->create();
        \App\Models\CompanySetting::factory()->create(['company_id' => $company->id]);
        $this->createActiveSubscription($company);

        $user = \App\Models\User::factory()->create([
            'company_id' => $company->id,
        ]);

        return [$company, $user];
    }
}
```

## Optimization Tips

### Slow Dashboard
1. Enable query logging: `DB_LOG_QUERIES=true`
2. Check for N+1 queries with `\DB::enableQueryLog()`
3. Add eager loading: `->with(['relationship'])`
4. Add database indexes on foreign keys
5. Implement caching for expensive queries

### High Query Count
```php
// Bad - N+1 query
foreach ($users as $user) {
    $user->team->name; // Executes query for each user
}

// Good - Eager loading
$users = User::with('team')->get();
foreach ($users as $user) {
    $user->team->name; // No additional queries
}
```

### Memory Issues
```php
// Bad - Loads all records into memory
$requests = VacationRequest::all();

// Good - Process in chunks
VacationRequest::chunk(100, function ($requests) {
    // Process 100 at a time
});
```

## CI/CD Integration

### GitHub Actions

```yaml
# .github/workflows/performance.yml
name: Performance Tests

on: [push, pull_request]

jobs:
  performance:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: shivammathur/setup-php@v2
        with:
          php-version: 8.3
      - run: composer install
      - run: php artisan test:performance --stop-on-failure
```

### GitLab CI

```yaml
# .gitlab-ci.yml
performance:
  stage: test
  script:
    - composer install
    - php artisan test:performance --stop-on-failure
```

## Monitoring in Production

While these tests run in the test environment, monitor similar metrics in production:

```bash
# Laravel Telescope
php artisan telescope:install

# Laravel Pulse
php artisan pulse:check

# Custom monitoring
php artisan monitor:performance
```

## Troubleshooting

### Tests Running Slow
- Check if running on slow hardware/VM
- Increase PHP memory limit: `memory_limit = 512M`
- Use faster database (SQLite for tests)
- Run tests in parallel: `php artisan test --parallel`

### Inconsistent Results
- Database state affecting results - ensure `RefreshDatabase` is used
- Background processes consuming resources
- Run tests multiple times and average results

### False Failures
- Adjust thresholds based on your environment
- CI/CD servers may be slower than local development
- Consider hardware differences when setting benchmarks

## Further Reading

- [LOAD_TESTING.md](../../LOAD_TESTING.md) - External load testing with k6 and Apache Bench
- [TESTING.md](../../TESTING.md) - General testing documentation
- [Laravel Performance](https://laravel.com/docs/12.x/deployment#optimization) - Official performance guide

---

**Last Updated:** 2026-01-20
