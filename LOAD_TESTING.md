# Load Testing Guide for Restio

This document provides comprehensive instructions for load testing the Restio application to ensure it can handle production traffic.

## Table of Contents
1. [Integrated Performance Tests](#integrated-performance-tests) ⭐ **NEW!**
2. [Prerequisites](#prerequisites)
3. [Local Load Testing](#local-load-testing)
4. [Production-like Testing](#production-like-testing)
5. [Test Scenarios](#test-scenarios)
6. [Metrics to Monitor](#metrics-to-monitor)
7. [Interpreting Results](#interpreting-results)
8. [Troubleshooting](#troubleshooting)

---

## Integrated Performance Tests

Restio now includes **built-in performance tests** that run directly within the Laravel test suite! These tests measure real application performance under various load conditions.

**⚠️ Important:** Performance tests use **PostgreSQL** (`restio_performance_test` database) to match production conditions, not SQLite. Regular unit/feature tests use SQLite in-memory for speed.

### Quick Start

```bash
# Run all performance tests
php artisan test:performance

# Run specific performance test
php artisan test:performance --filter=dashboard_loads_within_acceptable_time

# Run without confirmation prompt
php artisan test --testsuite=Performance
```

### What's Tested

The integrated performance test suite (`tests/Performance/`) includes:

#### 1. **Dashboard Performance** (`DashboardPerformanceTest.php`)
- ✅ Dashboard load time with 100 users, 1000 requests (target: < 500ms)
- ✅ Dashboard load time with 500 users, 5000 requests (target: < 1000ms)
- ✅ Dashboard load time with 2000 users, 20000 requests (target: < 2000ms)
- ✅ Concurrent dashboard requests (50 concurrent users, 5000+ requests)
- ✅ Database query count optimization (< 15 queries ideal, < 25 max)
- ✅ Memory usage monitoring (< 100MB with 1000 users, 16000+ requests)

#### 2. **API Endpoint Performance** (`ApiEndpointPerformanceTest.php`)
- ✅ Vacation request submission - 100 submissions with 2000 existing requests (target: < 500ms avg)
- ✅ Vacation request approval - 300 approvals with 9000+ requests (target: < 500ms avg)
- ✅ Team creation - 50 iterations (target: < 200ms avg)
- ✅ Bulk user assignment - 500 users to team (target: < 2000ms)
- ✅ Company settings update - 25 iterations (target: < 200ms avg)
- ✅ Notification dispatch performance - 20 employees (target: < 400ms)

### Performance Benchmarks

| Test | Data Volume | Target | Critical Threshold |
|------|-------------|--------|-------------------|
| Dashboard (Small) | 100 users, 1000 requests | < 500ms | < 750ms |
| Dashboard (Medium) | 500 users, 5000 requests | < 1000ms | < 1500ms |
| Dashboard (Large) | 2000 users, 20000 requests | < 2000ms | < 3000ms |
| Concurrent Requests (P95) | 50 users, 5000+ requests | < 1500ms | < 2500ms |
| Request Submission | 100 submissions, 2000 existing | < 500ms avg | < 750ms avg |
| Request Approval | 300 approvals, 9000+ existing | < 500ms avg | < 750ms avg |
| Bulk User Assignment | 500 users to team | < 2000ms | < 3000ms |
| Query Count | Any page load | < 15 | < 25 |
| Memory Usage | 1000 users, 16000+ requests | < 100MB | < 150MB |

### Example Output

```bash
$ php artisan test:performance

🚀 Running Performance Tests for Restio

System Information:
  • PHP Version: 8.3.6
  • Laravel Version: 12.0.0
  • Test Database: PostgreSQL (restio_performance_test)
  • Test Environment: testing
  • Memory Limit: 512M

Note: Tests use PostgreSQL for production-like conditions

═══════════════════════════════════════════════════════════
Starting Performance Test Suite
═══════════════════════════════════════════════════════════

 PASS  Tests\Performance\DashboardPerformanceTest
  ✓ dashboard loads within acceptable time for small company
    100 users, 1000 requests: 285.23ms
  ✓ dashboard loads within acceptable time for medium company
    500 users, 5000 requests: 712.67ms
  ✓ dashboard loads within acceptable time for large company
    2000 users, 20000 requests: 1523.45ms
  ✓ dashboard handles concurrent requests
    Average: 256.34ms | P95: 445.12ms | Max: 578.34ms (50 requests)
  ✓ dashboard query count is optimized
    ✅ Query count: 12
  ✓ dashboard memory usage is acceptable
    Memory used: 78.45MB for 1000 users, 16000+ requests

 PASS  Tests\Performance\ApiEndpointPerformanceTest
  ✓ vacation request submission is fast
    Average: 287.45ms | P95: 389.23ms | Max: 456.78ms (100 submissions)
  ✓ vacation request approval is fast
    Average: 312.67ms | P95: 425.89ms | Max: 487.34ms (300 approvals)
  ✓ bulk user assignment is fast
    Assigned 500 users in 1234.56ms

═══════════════════════════════════════════════════════════
✅ All performance tests passed!
⏱️  Total execution time: 15.23s
═══════════════════════════════════════════════════════════

📊 Performance Benchmarks:
...
```

### CI/CD Integration

Add performance tests to your CI/CD pipeline:

```yaml
# .github/workflows/tests.yml
- name: Run Performance Tests
  run: php artisan test:performance --stop-on-failure
```

### Custom Performance Tests

Create your own performance tests:

```bash
php artisan make:test Performance/YourFeaturePerformanceTest
```

```php
<?php

namespace Tests\Performance;

use Tests\Concerns\CreatesSubscriptions;
use Tests\TestCase;

class YourFeaturePerformanceTest extends TestCase
{
    use CreatesSubscriptions;

    public function test_your_feature_is_fast(): void
    {
        $start = microtime(true);

        // Your test code here

        $end = microtime(true);
        $loadTime = ($end - $start) * 1000; // ms

        $this->assertLessThan(300, $loadTime,
            "Feature took {$loadTime}ms (exceeds 300ms)"
        );
    }
}
```

### Tips for Performance Testing

1. **Run on representative data** - Use `ComprehensiveTestDataSeeder` to create realistic test data
2. **Test in isolation** - Run performance tests separately from feature tests
3. **Baseline first** - Record baseline performance before optimizations
4. **Test after changes** - Re-run performance tests after code changes
5. **Monitor trends** - Track performance metrics over time

---

## Prerequisites

### Required Tools

**1. Apache Bench (ab)** - Built into most Linux/Mac systems
```bash
# Check if installed
ab -V

# Install on Ubuntu/Debian
sudo apt-get install apache2-utils

# Install on Mac
brew install ab
```

**2. k6 (Recommended for advanced testing)**
```bash
# Install on Linux
sudo gpg -k
sudo gpg --no-default-keyring --keyring /usr/share/keyrings/k6-archive-keyring.gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69
echo "deb [signed-by=/usr/share/keyrings/k6-archive-keyring.gpg] https://dl.k6.io/deb stable main" | sudo tee /etc/apt/sources.list.d/k6.list
sudo apt-get update
sudo apt-get install k6

# Install on Mac
brew install k6

# Install on Windows
choco install k6
```

**3. PHP Memory Increase**
Update `php.ini` for load testing:
```ini
memory_limit = 512M
max_execution_time = 300
```

---

## Local Load Testing

### 1. Prepare Test Environment

```bash
# Set up test database
php artisan migrate:fresh --seed --env=testing

# Start Laravel server
php artisan serve --port=8000

# Or use Sail
./vendor/bin/sail up
```

### 2. Create Test User

```bash
# Run tinker to create a test user
php artisan tinker
```

```php
$company = \App\Models\Company::factory()->create();
\App\Models\CompanySetting::factory()->create(['company_id' => $company->id]);

$user = \App\Models\User::factory()->create([
    'email' => 'loadtest@example.com',
    'password' => bcrypt('password'),
    'company_id' => $company->id,
    'role' => 'employee',
]);

echo "User ID: {$user->id}\n";
echo "Company ID: {$company->id}\n";
exit;
```

### 3. Run Basic Load Tests

**Test 1: Homepage Load (Unauthenticated)**
```bash
ab -n 1000 -c 50 http://localhost:8000/
```

**Test 2: Dashboard Load (Authenticated)**
First, obtain a session cookie:
```bash
# Login and capture cookies
curl -c cookies.txt -X POST http://localhost:8000/login \
  -d "email=loadtest@example.com" \
  -d "password=password"

# Run load test with authentication
ab -n 1000 -c 50 -C "laravel_session=$(cat cookies.txt | grep laravel_session | awk '{print $7}')" \
  http://localhost:8000/dashboard
```

**Test 3: API Endpoint Load**
```bash
# Test vacation requests endpoint
ab -n 500 -c 25 -C "laravel_session=YOUR_SESSION_COOKIE" \
  http://localhost:8000/requests
```

---

## Production-like Testing

### Using k6 for Realistic Load Patterns

Create `load-test-scenarios.js`:

```javascript
import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate } from 'k6/metrics';

const failureRate = new Rate('failed_requests');

export let options = {
  stages: [
    { duration: '2m', target: 10 },   // Ramp up to 10 users
    { duration: '5m', target: 50 },   // Stay at 50 users for 5 minutes
    { duration: '2m', target: 100 },  // Ramp up to 100 users
    { duration: '5m', target: 100 },  // Stay at 100 users for 5 minutes
    { duration: '2m', target: 0 },    // Ramp down to 0 users
  ],
  thresholds: {
    http_req_duration: ['p(95)<500'], // 95% of requests must complete below 500ms
    'failed_requests': ['rate<0.1'],   // Error rate must be below 10%
  },
};

export default function () {
  // Homepage
  let res = http.get('http://localhost:8000/');
  check(res, {
    'homepage status is 200': (r) => r.status === 200,
  });
  failureRate.add(res.status !== 200);
  sleep(1);

  // Login
  res = http.post('http://localhost:8000/login', {
    email: 'loadtest@example.com',
    password: 'password',
  });
  check(res, {
    'login successful': (r) => r.status === 302 || r.status === 200,
  });
  failureRate.add(res.status !== 302 && res.status !== 200);

  let sessionCookie = res.cookies['laravel_session'][0].value;
  let params = {
    cookies: {
      laravel_session: sessionCookie,
    },
  };

  sleep(2);

  // Dashboard
  res = http.get('http://localhost:8000/dashboard', params);
  check(res, {
    'dashboard accessible': (r) => r.status === 200,
  });
  failureRate.add(res.status !== 200);
  sleep(2);

  // Requests page
  res = http.get('http://localhost:8000/requests', params);
  check(res, {
    'requests page accessible': (r) => r.status === 200,
  });
  failureRate.add(res.status !== 200);
  sleep(2);

  // Calendar
  res = http.get('http://localhost:8000/calendar', params);
  check(res, {
    'calendar accessible': (r) => r.status === 200,
  });
  failureRate.add(res.status !== 200);
  sleep(3);
}
```

**Run the k6 test:**
```bash
k6 run load-test-scenarios.js
```

---

## Test Scenarios

### Scenario 1: Concurrent User Logins (50-100 users)

**Objective:** Test authentication system under load

```bash
# Using ab
ab -n 1000 -c 100 -p login-data.txt -T application/x-www-form-urlencoded \
  http://localhost:8000/login
```

Create `login-data.txt`:
```
email=loadtest@example.com&password=password
```

### Scenario 2: Dashboard with Database Queries

**Objective:** Test dashboard performance with real data

```javascript
// k6 script: dashboard-load.js
import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
  vus: 100,        // 100 virtual users
  duration: '10m', // Run for 10 minutes
};

export default function () {
  const sessionCookie = 'YOUR_VALID_SESSION_COOKIE';

  let params = {
    cookies: {
      laravel_session: sessionCookie,
    },
  };

  let res = http.get('http://localhost:8000/dashboard', params);
  check(res, {
    'status is 200': (r) => r.status === 200,
    'response time < 500ms': (r) => r.timings.duration < 500,
  });

  sleep(Math.random() * 3 + 2); // Sleep 2-5 seconds
}
```

### Scenario 3: Vacation Request Creation

**Objective:** Test write operations under load

```javascript
// k6 script: create-requests.js
import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
  vus: 50,
  duration: '5m',
};

export default function () {
  const sessionCookie = 'YOUR_VALID_SESSION_COOKIE';
  const csrfToken = 'YOUR_CSRF_TOKEN';

  let payload = JSON.stringify({
    start_date: '2026-02-01',
    end_date: '2026-02-05',
    type: 'vacation',
    reason: 'Load testing vacation request',
  });

  let params = {
    cookies: {
      laravel_session: sessionCookie,
    },
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
    },
  };

  let res = http.post('http://localhost:8000/vacation-requests', payload, params);
  check(res, {
    'request created': (r) => r.status === 302 || r.status === 201,
  });

  sleep(5);
}
```

### Scenario 4: Calendar View with Many Employees

**Objective:** Test calendar performance with 50+ employees

**Setup:**
```bash
php artisan tinker
```

```php
$company = \App\Models\Company::first();

// Create 100 employees
for ($i = 0; $i < 100; $i++) {
    $user = \App\Models\User::factory()->create(['company_id' => $company->id]);

    // Create 5 vacation requests per employee
    \App\Models\VacationRequest::factory()->count(5)->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
    ]);
}

echo "Created 100 employees with 500 vacation requests\n";
exit;
```

**Test:**
```bash
ab -n 500 -c 50 -C "laravel_session=YOUR_SESSION" \
  http://localhost:8000/calendar
```

---

## Metrics to Monitor

### Application Metrics

1. **Response Time**
   - P50 (median): < 200ms
   - P95: < 500ms
   - P99: < 1000ms

2. **Throughput**
   - Requests per second (RPS)
   - Target: > 100 RPS for authenticated requests

3. **Error Rate**
   - Target: < 1% errors
   - Monitor 4xx and 5xx responses

### System Metrics

Monitor during load tests:

```bash
# CPU usage
top -b -n 1 | head -20

# Memory usage
free -h

# Database connections
# MySQL/MariaDB
mysql -u root -p -e "SHOW PROCESSLIST;"

# PostgreSQL
psql -U postgres -c "SELECT count(*) FROM pg_stat_activity;"

# Laravel logs
tail -f storage/logs/laravel.log

# Nginx/Apache access logs
tail -f /var/log/nginx/access.log
```

### Database Query Performance

```bash
# Enable query logging in .env
DB_LOG_QUERIES=true

# Run load test, then check slow queries
php artisan db:slow-queries
```

### Cache Hit Ratio

```bash
# Redis
redis-cli info stats | grep keyspace

# Check Laravel cache performance
php artisan cache:stats
```

---

## Interpreting Results

### Good Performance Indicators

✅ Response times under target thresholds
✅ Error rate < 1%
✅ CPU usage < 70%
✅ Memory usage stable (no leaks)
✅ Database connections < max pool size
✅ Cache hit ratio > 90%
✅ No failed requests

### Warning Signs

⚠️ Response times increasing over time
⚠️ Error rate > 1%
⚠️ CPU constantly > 80%
⚠️ Memory usage climbing
⚠️ Database connection pool exhausted
⚠️ Frequent 502/504 errors
⚠️ Queue backlog growing

### Critical Issues

🚨 Application crashes
🚨 Database deadlocks
🚨 Out of memory errors
🚨 Connection timeouts
🚨 Error rate > 10%
🚨 Response times > 5 seconds

---

## Troubleshooting

### Issue: High Response Times

**Diagnosis:**
```bash
# Enable query log
DB_LOG_QUERIES=true

# Check for N+1 queries
php artisan debugbar:enable

# Profile with Telescope
php artisan telescope:install
```

**Solutions:**
- Enable caching (Redis/Memcached)
- Add database indexes
- Optimize N+1 queries with eager loading
- Implement pagination

### Issue: Memory Leaks

**Diagnosis:**
```bash
# Monitor memory during test
watch -n 1 free -h

# Check PHP memory limit
php -i | grep memory_limit
```

**Solutions:**
- Increase PHP memory_limit
- Use chunk() for large datasets
- Clear unnecessary variables
- Use generators for large data processing

### Issue: Database Bottlenecks

**Diagnosis:**
```bash
# Check active connections
mysql> SHOW PROCESSLIST;

# Check slow queries
mysql> SHOW FULL PROCESSLIST;
mysql> SELECT * FROM mysql.slow_log;
```

**Solutions:**
- Add indexes to frequently queried columns
- Optimize complex queries
- Increase database connection pool
- Consider read replicas
- Enable query caching

### Issue: Queue Backlog

**Diagnosis:**
```bash
# Check queue size
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed
```

**Solutions:**
- Increase queue workers
- Optimize job processing
- Use job batching
- Consider Horizon for Redis queues

---

## Performance Benchmarks

### Target Performance (Production)

| Metric | Target | Critical Threshold |
|--------|--------|-------------------|
| Dashboard Load | < 200ms | < 500ms |
| Calendar Page | < 300ms | < 800ms |
| Vacation Request Create | < 150ms | < 400ms |
| Login | < 100ms | < 300ms |
| Concurrent Users | 100+ | 500+ |
| Requests/Second | 50+ | 200+ |
| Database Queries per Request | < 20 | < 50 |
| Cache Hit Ratio | > 90% | > 70% |

### Optimization Checklist

- [ ] Redis/Memcached caching enabled
- [ ] Database indexes on foreign keys
- [ ] N+1 queries eliminated
- [ ] Pagination implemented for large lists
- [ ] Assets minified and cached
- [ ] CDN for static assets
- [ ] PHP OpCache enabled
- [ ] Database query cache enabled
- [ ] Gzip compression enabled
- [ ] HTTP/2 enabled
- [ ] Session driver optimized (Redis/Database)
- [ ] Queue workers running
- [ ] Job batching implemented
- [ ] Rate limiting configured

---

## Sample Load Test Results

### Good Result Example

```
Concurrency Level:      100
Time taken for tests:   10.234 seconds
Complete requests:      1000
Failed requests:        0
Total transferred:      2450000 bytes
HTML transferred:       1890000 bytes
Requests per second:    97.71 [#/sec] (mean)
Time per request:       1023.400 [ms] (mean)
Time per request:       10.234 [ms] (mean, across all concurrent requests)
Transfer rate:          233.78 [Kbytes/sec] received

Percentage of the requests served within a certain time (ms)
  50%    850
  66%    920
  75%    980
  80%   1020
  90%   1150
  95%   1280
  98%   1450
  99%   1580
 100%   2100 (longest request)
```

✅ All requests succeeded
✅ 95% of requests under 1.3s
✅ No errors

---

## Next Steps

1. **Run baseline tests** before any optimizations
2. **Implement performance improvements** from PERFORMANCE.md
3. **Re-run tests** to measure improvements
4. **Monitor production** with same metrics
5. **Set up alerts** for performance degradation

## Additional Resources

- [Laravel Performance Best Practices](https://laravel.com/docs/12.x/deployment#optimization)
- [k6 Documentation](https://k6.io/docs/)
- [Apache Bench Documentation](https://httpd.apache.org/docs/2.4/programs/ab.html)
- [Database Query Optimization](https://use-the-index-luke.com/)

---

---

## Recent Performance Optimizations

See [PERFORMANCE_OPTIMIZATIONS.md](PERFORMANCE_OPTIMIZATIONS.md) for details on recent optimizations that achieved:
- **98% improvement** in dashboard load times
- **Query count reduced** from 37 to 29
- **Memory usage reduced** by 73%
- All performance tests now passing with production-scale data

---

**Last Updated:** 2026-01-21
**Version:** 1.1.0
