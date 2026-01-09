# Testing Patterns

**Analysis Date:** 2026-01-09

## Test Framework

**Runner:**
- PHPUnit 11.5.3 (`composer.json`)
- Config: `phpunit.xml` in project root

**Assertion Library:**
- PHPUnit built-in assertions
- Laravel testing helpers (`assertStatus`, `assertRedirect`, `assertSessionHasErrors`)

**Run Commands:**
```bash
php artisan test                              # Run all tests
php artisan test tests/Feature/ExampleTest.php  # Run specific file
php artisan test --filter=testName            # Filter by test name
php artisan test --parallel                   # Run in parallel
```

## Test File Organization

**Location:**
- Feature tests: `tests/Feature/`
- Unit tests: `tests/Unit/`
- Base class: `tests/TestCase.php`

**Naming:**
- Test classes: `{Feature}Test.php`
- Test methods: `test_{describes_what_happens}()` (snake_case)

**Structure:**
```
tests/
├── TestCase.php                    # Base test class
├── Feature/
│   ├── Auth/
│   │   ├── AuthenticationTest.php
│   │   ├── EmailVerificationTest.php
│   │   ├── PasswordResetTest.php
│   │   ├── PasswordConfirmationTest.php
│   │   ├── RegistrationTest.php
│   │   ├── TwoFactorChallengeTest.php
│   │   └── VerificationNotificationTest.php
│   ├── Settings/
│   │   ├── ProfileUpdateTest.php
│   │   ├── PasswordUpdateTest.php
│   │   └── TwoFactorAuthenticationTest.php
│   ├── DashboardTest.php
│   └── ExampleTest.php
└── Unit/
    └── ExampleTest.php
```

## Test Structure

**Suite Organization:**
```php
<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('profile.edit'));

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
    }
}
```

**Patterns:**
- `use RefreshDatabase` trait for database reset per test
- `actingAs($user)` for authenticated requests
- `$user->refresh()` or `$user->fresh()` to reload from database
- Chained assertions on response

## Mocking

**Framework:**
- Mockery 1.6 (included via `composer.json`)
- Laravel's built-in mocking helpers

**Patterns:**
```php
// Mock a facade
Mail::fake();
Mail::assertSent(OrderShipped::class);

// Mock time
$this->travel(5)->minutes();
$this->travelTo(now()->addMonth());

// Mock queue
Queue::fake();
Queue::assertPushed(ProcessPayment::class);
```

**What to Mock:**
- Email sending (`Mail::fake()`)
- Queue jobs (`Queue::fake()`)
- Time-sensitive operations (`travel()`)
- External services

**What NOT to Mock:**
- Database (use RefreshDatabase trait)
- Internal models and services
- Validation logic

## Fixtures and Factories

**Factory Pattern:**
```php
// Create a user
$user = User::factory()->create();

// Create with specific attributes
$user = User::factory()->create([
    'name' => 'Test User',
    'email' => 'test@example.com',
]);

// Create with relationship
$company = Company::factory()->create();
$user = User::factory()->for($company)->create();

// Use factory states
$request = VacationRequest::factory()->approved()->create();
$request = VacationRequest::factory()->pending()->create();
$request = VacationRequest::factory()->rejected()->create();
```

**Available Factories:**
- `UserFactory.php` - Users with default company
- `CompanyFactory.php` - Companies
- `CompanySettingFactory.php` - Company settings
- `VacationRequestFactory.php` - Vacation requests with states

**Location:**
- Factories: `database/factories/`
- No separate fixtures directory

## Coverage

**Requirements:**
- No enforced coverage target
- Focus on feature tests for critical paths

**Configuration:**
- Source inclusion: `<directory>app</directory>` in `phpunit.xml`
- Exclusions: Not explicitly configured

**View Coverage:**
```bash
php artisan test --coverage
php artisan test --coverage-html coverage/
```

## Test Types

**Unit Tests:**
- Scope: Single class/function in isolation
- Location: `tests/Unit/`
- Mocking: Heavy mocking of dependencies
- Example: `ExampleTest.php`

**Feature Tests:**
- Scope: Full HTTP request/response cycle
- Location: `tests/Feature/`
- Database: Uses `RefreshDatabase` trait
- Examples: Auth tests, Settings tests, Dashboard tests

**E2E Tests:**
- Not currently configured
- Could use Laravel Dusk or Playwright

## Common Patterns

**Auth Testing:**
```php
// Guest access
$response = $this->get('/dashboard');
$response->assertRedirect('/login');

// Authenticated access
$response = $this->actingAs($user)->get('/dashboard');
$response->assertOk();
```

**Form Submission:**
```php
$response = $this
    ->actingAs($user)
    ->patch(route('profile.update'), [
        'name' => 'New Name',
        'email' => 'new@example.com',
    ]);

$response
    ->assertSessionHasNoErrors()
    ->assertRedirect(route('profile.edit'));
```

**Validation Testing:**
```php
$response = $this
    ->actingAs($user)
    ->patch(route('profile.update'), [
        'name' => '',  // Invalid - empty
        'email' => 'not-an-email',  // Invalid format
    ]);

$response->assertSessionHasErrors(['name', 'email']);
```

**Database Assertions:**
```php
$this->assertDatabaseHas('users', [
    'email' => 'test@example.com',
]);

$this->assertDatabaseMissing('users', [
    'email' => 'deleted@example.com',
]);

$this->assertDatabaseCount('users', 5);
```

## Test Environment

**Configuration (`phpunit.xml`):**
```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="pgsql"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="CACHE_STORE" value="array"/>
    <env name="SESSION_DRIVER" value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
    <env name="MAIL_MAILER" value="array"/>
    <env name="PULSE_ENABLED" value="false"/>
    <env name="TELESCOPE_ENABLED" value="false"/>
</php>
```

**Key Settings:**
- In-memory database for speed
- Array drivers for cache/session (no persistence)
- Sync queue (immediate execution)
- Mail array (no actual sending)

---

*Testing analysis: 2026-01-09*
*Update when test patterns change*
