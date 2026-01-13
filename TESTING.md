# Testing Guide

This project includes automated tests using PHPUnit and PostgreSQL via Docker.

## Quick Start

The easiest way to run tests is using the provided test script:

```bash
./test.sh
```

This will:
1. Start a PostgreSQL test database in Docker
2. Wait for the database to be ready
3. Run migrations
4. Execute all tests
5. Stop the test database

## Test Commands

### Using the Test Script

```bash
# Run all tests
./test.sh

# Run tests and keep database running (useful for debugging)
./test.sh --keep

# Run specific tests
./test.sh --filter=OnboardingFlowTest

# Run tests with any PHPUnit options
./test.sh --filter=test_user_can_login --stop-on-failure
```

### Using Make

```bash
# Run all tests
make test

# Run tests and keep database running
make test-keep

# Run specific test filter
make test-filter filter=OnboardingFlowTest

# Start test database only
make test-db-up

# Stop test database
make test-db-down

# Restart test database
make test-db-restart

# Run migrations on test database
make test-migrate

# Run tests without Docker (uses existing database)
make test-local
```

### Manual Testing

If you prefer to manage the database manually:

```bash
# 1. Start the test database
docker compose -f docker-compose.testing.yml up -d

# 2. Wait for database to be ready
docker compose -f docker-compose.testing.yml exec db-test pg_isready -U laravel_test

# 3. Run migrations
php artisan migrate:fresh --env=testing

# 4. Run tests
php artisan test --env=testing

# 5. Stop the database
docker compose -f docker-compose.testing.yml down
```

## Test Database Configuration

The test database runs on **port 5433** (different from the main database on 5432) to avoid conflicts.

### Database Credentials

- **Host:** localhost
- **Port:** 5433
- **Database:** laravel_test
- **Username:** laravel_test
- **Password:** secret_test

These are configured in `.env.testing` (which is git-ignored).

## Setting Up Your Environment

1. Copy the example testing environment file:
   ```bash
   cp .env.testing.example .env.testing
   ```

2. Update `.env.testing` with your APP_KEY:
   ```bash
   php artisan key:generate --env=testing
   ```

## Test Database Details

The test database uses:
- **PostgreSQL 16** (same as production for consistency)
- **tmpfs storage** for speed (data is stored in RAM)
- **Health checks** to ensure database is ready before tests run

## Debugging Failed Tests

If tests fail, you can keep the database running to inspect the data:

```bash
# Tests failed, database keeps running automatically
./test.sh

# Or explicitly keep it running
./test.sh --keep
```

Then connect to the database:

```bash
# Using psql
psql -h localhost -p 5433 -U laravel_test laravel_test

# Or using Docker
docker compose -f docker-compose.testing.yml exec db-test psql -U laravel_test
```

## CI/CD Integration

The test setup is designed to work in CI/CD environments:

```yaml
# Example GitHub Actions workflow
- name: Start test database
  run: docker compose -f docker-compose.testing.yml up -d

- name: Run tests
  run: |
    php artisan migrate:fresh --env=testing
    php artisan test --env=testing

- name: Cleanup
  run: docker compose -f docker-compose.testing.yml down
```

## Writing Tests

Tests are located in:
- `tests/Feature/` - Feature tests (most tests should go here)
- `tests/Unit/` - Unit tests

Example test structure:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_example(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
```

## Common Issues

### Database Connection Failed

If you see connection errors:

```bash
# Check if database is running
docker compose -f docker-compose.testing.yml ps

# Check database logs
docker compose -f docker-compose.testing.yml logs db-test

# Restart the database
make test-db-restart
```

### Port Conflict

If port 5433 is already in use:

1. Update the port in `docker-compose.testing.yml`
2. Update `DB_PORT` in `.env.testing`

### Slow Tests

The database uses tmpfs (RAM storage) for speed. If tests are still slow:

- Use `--filter` to run specific test suites
- Check for N+1 queries using Laravel Debugbar or Telescope
- Reduce `BCRYPT_ROUNDS` in `.env.testing` (already set to 4)
