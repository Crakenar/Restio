# Laravel Horizon & Queue Setup

Complete guide for Laravel Horizon with Docker, Redis, and optimized queue processing.

---

## 🚀 What's Been Configured

### ✅ Horizon Installed & Configured
- Laravel Horizon v5.43 installed
- Multi-queue support (default, notifications, emails)
- Auto-scaling workers based on workload
- Protected dashboard with authentication
- Supervisor configuration for production

### ✅ Redis Queue Backend
- Redis 7 configured for queues & cache
- Persistent data storage
- Health checks enabled
- Docker service properly configured

### ✅ Docker Setup
- `horizon` service replaces old `queue` worker
- Auto-restart on failure
- Volume mounting for hot-reloading
- Proper dependency management

---

## 📊 Horizon Dashboard

**Access:** `http://localhost/horizon` (after login)

**Features:**
- Real-time job monitoring
- Failed jobs management
- Job metrics and throughput
- Queue wait times
- Worker process monitoring
- Auto-pruning old jobs

---

## 🔧 Configuration

### Queue Connections (.env)

```env
# Queue Configuration
QUEUE_CONNECTION=redis

# Cache Configuration (using Redis)
CACHE_STORE=redis
CACHE_PREFIX=restio_cache

# Redis Configuration
REDIS_CLIENT=phpredis
REDIS_HOST=redis  # Docker service name
REDIS_PASSWORD=null
REDIS_PORT=6379

# Horizon Settings
HORIZON_NAME=Restio
HORIZON_PATH=horizon
```

### Queue Definitions (config/horizon.php)

**Production Environment:**
- **Default Queue**: 10 workers, auto-scaling
- **Notifications Queue**: 5 workers
- **Emails Queue**: 3 workers

**Local Environment:**
- **Default Queue**: 3 workers

---

## 🏗️ Architecture

### Queue Processing Flow

```
Laravel App → Dispatch Job → Redis Queue
                                  ↓
                            Horizon Workers
                                  ↓
                           Process Job Async
                                  ↓
                          Success or Failure
                                  ↓
                        Horizon Dashboard Metrics
```

### Worker Configuration

| Environment | Queue | Workers | Tries | Timeout |
|-------------|-------|---------|-------|---------|
| Production | default | 10 (auto-scale) | 3 | 90s |
| Production | notifications | 5 | 3 | 60s |
| Production | emails | 3 | 3 | 120s |
| Local | default | 3 | 1 | 60s |

---

## 🐳 Docker Commands

### Start All Services
```bash
docker compose up -d
```

### Rebuild After Changes
```bash
docker compose build app
docker compose up -d --force-recreate horizon
```

### View Horizon Logs
```bash
docker compose logs -f horizon
```

### Restart Horizon
```bash
docker compose restart horizon
```

### Check Running Services
```bash
docker compose ps
```

### Access App Container
```bash
docker compose exec app bash
```

---

## 📝 Creating Jobs

### 1. Generate Job Class
```bash
php artisan make:job SendVacationApprovalNotification
```

### 2. Configure Job

```php
<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendVacationApprovalNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public VacationRequest $request
    ) {
        // Specify queue
        $this->onQueue('notifications');

        // Set retries
        $this->tries = 3;

        // Set timeout
        $this->timeout = 60;
    }

    public function handle(): void
    {
        // Send notification
        $this->user->notify(new VacationApprovedNotification($this->request));
    }

    public function failed(\Throwable $exception): void
    {
        // Handle failure
        \Log::error('Failed to send approval notification', [
            'user' => $this->user->id,
            'request' => $this->request->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

### 3. Dispatch Job

```php
// In your controller
SendVacationApprovalNotification::dispatch($user, $request);

// Dispatch to specific queue
SendVacationApprovalNotification::dispatch($user, $request)
    ->onQueue('notifications');

// Delayed dispatch
SendVacationApprovalNotification::dispatch($user, $request)
    ->delay(now()->addMinutes(5));

// High priority
SendVacationApprovalNotification::dispatch($user, $request)
    ->onQueue('high');
```

---

## 🎯 Queue Priority

Jobs are processed in this order:

1. **High Priority** - Critical operations
2. **Notifications** - User notifications
3. **Emails** - Email sending
4. **Default** - General background tasks

### Example Priority Usage

```php
// High priority notification
SendUrgentNotification::dispatch($data)->onQueue('high');

// Normal priority email
SendWelcomeEmail::dispatch($user)->onQueue('emails');

// Background cleanup
CleanupOldRecords::dispatch()->onQueue('default');
```

---

## 📈 Monitoring & Maintenance

### Horizon Commands

```bash
# Start Horizon
php artisan horizon

# Terminate Horizon gracefully
php artisan horizon:terminate

# Pause all workers
php artisan horizon:pause

# Continue all workers
php artisan horizon:continue

# Check status
php artisan horizon:status

# Purge all failed jobs
php artisan horizon:purge
```

### Failed Jobs

**View failed jobs:**
1. Go to `/horizon`
2. Click "Failed Jobs" tab
3. View error details
4. Retry or delete

**Retry failed jobs:**
```bash
# Retry all failed jobs
php artisan horizon:clear

# Or via dashboard
```

### Metrics & Monitoring

**Horizon Dashboard shows:**
- Jobs per minute
- Wait time per queue
- Throughput
- Recent jobs
- Failed jobs
- Memory usage

---

## 🛠️ Troubleshooting

### Horizon Not Processing Jobs

```bash
# Check if Horizon is running
docker compose ps horizon

# Check logs
docker compose logs horizon

# Restart Horizon
docker compose restart horizon
```

### Redis Connection Issues

```bash
# Test Redis connection
docker compose exec app php artisan tinker
> \Redis::connection()->ping();

# Check Redis is running
docker compose ps redis
```

### Jobs Failing

1. Check `/horizon` dashboard for error details
2. Check `storage/logs/laravel.log`
3. Check `storage/logs/horizon.log`
4. Verify job dependencies are available
5. Check timeout and memory limits

### Clear Queue

```bash
# Clear all queued jobs
php artisan queue:clear redis

# Clear specific queue
php artisan queue:clear redis --queue=notifications
```

---

## 🔒 Security

### Dashboard Protection

Horizon dashboard is protected by authentication middleware:

```php
// config/horizon.php
'middleware' => ['web', 'auth'],
```

**Only authenticated users can access `/horizon`**

### Production Recommendations

1. **Use Authorization Gate:**

```php
// app/Providers/HorizonServiceProvider.php
use Laravel\Horizon\Horizon;

protected function gate(): void
{
    Horizon::auth(function ($request) {
        return in_array($request->user()?->email, [
            'admin@example.com',
        ]) || $request->user()?->role === 'owner';
    });
}
```

2. **Enable HTTPS**
3. **Restrict network access**
4. **Use strong Redis password in production**

---

## ⚡ Performance Tips

### 1. Queue Specific Jobs
```php
// Group similar jobs on same queue
SendEmailJob::dispatch()->onQueue('emails');
ProcessImageJob::dispatch()->onQueue('media');
```

### 2. Optimize Job Payload
```php
// Don't serialize large models
public function __construct(public int $userId) {} // ✅ Good

public function __construct(public User $user) {} // ❌ Can be slow
```

### 3. Use Job Batching
```php
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

Bus::batch([
    new ProcessReport($user1),
    new ProcessReport($user2),
    new ProcessReport($user3),
])->dispatch();
```

### 4. Set Appropriate Timeouts
```php
// Long-running jobs
public int $timeout = 300; // 5 minutes

// Quick jobs
public int $timeout = 30; // 30 seconds
```

---

## 📊 Example Jobs

### 1. Send Notification
```php
// app/Jobs/SendVacationRequestNotification.php
SendVacationRequestNotification::dispatch($manager, $request)
    ->onQueue('notifications');
```

### 2. Send Email
```php
// app/Jobs/SendWelcomeEmail.php
SendWelcomeEmail::dispatch($user)
    ->onQueue('emails')
    ->delay(now()->addHours(1));
```

### 3. Generate Report
```php
// app/Jobs/GenerateMonthlyReport.php
GenerateMonthlyReport::dispatch($company)
    ->onQueue('default');
```

### 4. Bulk Operations
```php
// Process multiple users
foreach ($users as $user) {
    UpdateUserVacationBalance::dispatch($user)
        ->onQueue('default')
        ->delay(now()->addSeconds($loop->index * 2));
}
```

---

## 🎓 Best Practices

### 1. **Idempotent Jobs**
Jobs should produce the same result when run multiple times:
```php
public function handle(): void
{
    // Check if already processed
    if ($this->request->is_processed) {
        return;
    }

    // Process...
    $this->request->update(['is_processed' => true]);
}
```

### 2. **Small Payloads**
Pass IDs instead of full models:
```php
// ✅ Good
public function __construct(public int $userId) {}

public function handle(): void
{
    $user = User::find($this->userId);
}

// ❌ Bad - serializes entire model
public function __construct(public User $user) {}
```

### 3. **Handle Failures Gracefully**
```php
public function failed(\Throwable $exception): void
{
    Log::error('Job failed', [
        'exception' => $exception->getMessage(),
        'job' => self::class,
    ]);

    // Notify admin
    AdminNotification::send($exception);
}
```

### 4. **Use Rate Limiting**
```php
use Illuminate\Support\Facades\RateLimiter;

public function handle(): void
{
    RateLimiter::attempt(
        'send-email:'.$this->user->id,
        $maxAttempts = 5,
        function() {
            // Send email
        }
    );
}
```

---

## 📦 Production Deployment

### 1. Build Image
```bash
docker compose build app
```

### 2. Update Environment
```env
APP_ENV=production
QUEUE_CONNECTION=redis
REDIS_HOST=redis
HORIZON_NAME=Restio-Production
```

### 3. Deploy
```bash
docker compose up -d
```

### 4. Verify
```bash
# Check Horizon is running
docker compose ps horizon

# Check logs
docker compose logs -f horizon
```

---

## 🔗 Useful Links

- [Horizon Documentation](https://laravel.com/docs/12.x/horizon)
- [Queue Documentation](https://laravel.com/docs/12.x/queues)
- [Redis Documentation](https://redis.io/documentation)

---

## 📌 Quick Reference

| Command | Description |
|---------|-------------|
| `docker compose up -d` | Start all services |
| `docker compose restart horizon` | Restart Horizon |
| `docker compose logs -f horizon` | View Horizon logs |
| `php artisan horizon` | Start Horizon manually |
| `php artisan horizon:terminate` | Stop Horizon gracefully |
| `php artisan queue:work` | Process queue manually |
| `php artisan queue:clear redis` | Clear all queued jobs |
| `php artisan horizon:purge` | Clear failed jobs |

---

**Last Updated:** 2026-01-21
**Version:** 1.0.0
**Laravel Horizon:** v5.43.0
