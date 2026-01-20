# Testing Documentation for Restio

Comprehensive testing guide for the Restio vacation management system.

## Test Suite Overview

Restio has a comprehensive test suite covering:
- **User Acceptance Tests (UAT)**: 8 tests covering complete user workflows
- **Security & Authorization**: 15 tests verifying access control and data isolation
- **Team Management**: 15 tests for team CRUD operations

### Test Statistics

| Category | Tests | Status |
|----------|-------|--------|
| UAT | 8 | ✅ All passing |
| Security | 15 | ✅ 14 passing, 1 risky |
| Team Management | 15 | ⚠️ Created (some auth issues in test env) |
| **Total** | **38** | Comprehensive coverage |

## Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/UserAcceptanceTest.php
php artisan test tests/Feature/SecurityAuthorizationTest.php

# Run with coverage
php artisan test --coverage
```

## Test Files Created

1. **tests/Feature/UserAcceptanceTest.php** - 8 UAT tests
2. **tests/Feature/SecurityAuthorizationTest.php** - 15 security tests
3. **tests/Feature/TeamManagementTest.php** - 15 team management tests

## Load Testing

See **LOAD_TESTING.md** for comprehensive load testing guide.

## Test Data

Run the comprehensive test data seeder:

```bash
php artisan db:seed --class=ComprehensiveTestDataSeeder
```

This creates:
- 3 companies (Small: 10 users, Medium: 58 users, Large: 174 users)
- Multiple teams per company
- 1000+ realistic vacation requests
- All users have password: **password**

**Test Logins:**
- Small: owner@smallstartup.com
- Medium: owner@mediumbusiness.com  
- Large: ceo@largeenterprise.com

**Last Updated:** 2026-01-20
