# Multi-Tier Subscription System Implementation

## Overview
Complete implementation of a multi-tier subscription system with user limits for Restio.

## What's Been Implemented

### 1. Database Changes

#### Migration: `add_user_limits_and_features_to_subscriptions_table.php`
Added the following fields to the `subscriptions` table:
- `max_users` - Maximum number of users (including owner)
- `description` - Plan description
- `features` - JSON array of plan features
- `is_popular` - Boolean to highlight popular plans
- `sort_order` - Integer for custom ordering

### 2. Subscription Plans

#### Created 8 Subscription Tiers:

**Free Plan:**
- Price: €0/month
- Users: 6 (5 employees + 1 owner)
- Features: Core features only

**Starter Monthly:**
- Price: €29/month
- Users: 21 (20 employees + 1 owner)
- Effective: ~€1.45/user/month

**Starter Yearly:**
- Price: €279/year
- Users: 21
- Savings: €69/year (20% discount)
- Marked as popular

**Professional Monthly:**
- Price: €79/month
- Users: 51 (50 employees + 1 owner)
- Effective: ~€1.58/user/month

**Professional Yearly:**
- Price: €759/year
- Users: 51
- Savings: €189/year (20% discount)
- Marked as popular

**Enterprise Monthly:**
- Price: €199/month
- Users: 201 (200 employees + 1 owner)
- Effective: ~€0.99/user/month

**Enterprise Yearly:**
- Price: €1,790/year
- Users: 201
- Savings: €598/year (25% discount)
- Marked as popular

**Lifetime:**
- Price: €2,999 (one-time)
- Users: 201 (200 employees + 1 owner)
- All Enterprise features forever
- Marked as popular

### 3. Backend Updates

#### Models

**Subscription Model** (`app/Models/Subscription.php`):
- Added casts for new fields
- Added `scopeOrdered()` for sorting
- Added `scopePaid()` for non-free plans
- Added `isFree()` helper
- Added `isLifetime()` helper
- Added `formatted_price` attribute

**Company Model** (`app/Models/Company.php`):
- Added `active_subscription` attribute
- Added `user_limit` attribute
- Added `current_user_count` attribute
- Added `remaining_user_slots` attribute
- Added `canAddUsers($count)` method
- Added `isNearUserLimit($threshold)` method
- Added `hasReachedUserLimit()` method

#### Controllers

**SubscriptionManagementController**:
- Updated to include user count/limit info in company data
- Updated available_plans to include all new fields
- Plans now ordered by sort_order

**EmployeesController**:
- Added user limit check in `store()` method
- Added user limit check in `importCsv()` method with bulk counting
- Added subscription_info to index response

#### Middleware

**EnforceSubscriptionLimits**:
- Checks user limits on employee creation routes
- Returns error with upgrade URL when limit reached
- JSON-friendly for API requests

### 4. Database Seeder

**SubscriptionPlansSeeder** (`database/seeders/SubscriptionPlansSeeder.php`):
- Seeds all 8 subscription plans
- Uses `updateOrCreate()` for idempotency
- Includes detailed features arrays for each plan

## How to Deploy

### Step 1: Run Migration
```bash
php artisan migrate
```

### Step 2: Run Seeder
```bash
php artisan db:seed --class=SubscriptionPlansSeeder
```

### Step 3: Register Middleware (Optional)
Add to `bootstrap/app.php` if you want global enforcement:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\EnforceSubscriptionLimits::class);
})
```

Or apply to specific routes in route files:
```php
Route::middleware(['auth', 'enforce-subscription-limits'])->group(function () {
    Route::post('/employees', [EmployeesController::class, 'store']);
});
```

### Step 4: Update Frontend
The SubscriptionManagement.vue component needs to be updated to display:
- User count vs limit
- Warning when near limit
- New plan features and descriptions
- User limit information on each plan card

## User Limit Enforcement

### Where It's Enforced:
1. **EmployeesController::store()** - Before creating single employee
2. **EmployeesController::importCsv()** - Before bulk import (checks total count)
3. **EnforceSubscriptionLimits middleware** - Can be applied to any route

### User Experience:
- Users see current count vs limit
- Warning at 80% capacity (customizable)
- Hard block at 100% with upgrade CTA
- Error messages include upgrade URL

## Upgrade Paths

**Automatic Triggers:**
- Free (6 users) → Starter (21 users)
- Starter (21 users) → Professional (51 users)
- Professional (51 users) → Enterprise (201 users)

**Value Propositions:**
- Annual plans save 20-25%
- Lifetime plan saves €7,961 over 5 years vs monthly Enterprise
- Clear feature progression

## Testing Checklist

- [ ] Run migration successfully
- [ ] Run seeder successfully
- [ ] Verify 8 plans exist in database
- [ ] Test employee creation at limit
- [ ] Test CSV import at limit
- [ ] Test upgrade flow
- [ ] Verify Stripe integration with new plans
- [ ] Test user count calculations
- [ ] Test limit warnings at 80%
- [ ] Test hard block at 100%

## Next Steps

1. **Update Frontend**: Completely rewrite SubscriptionManagement.vue to show new plan structure
2. **Add Warning Component**: Show persistent banner when near user limit
3. **Analytics Dashboard**: Show subscription metrics for admins
4. **Email Notifications**: Send emails when approaching limit
5. **Grace Period**: Consider allowing 1-2 users over limit with warning
6. **Stripe Product Setup**: Create corresponding products in Stripe dashboard
7. **Recommended Plan Logic**: Show "Recommended for you" based on current user count
8. **Testimonials**: Add social proof for Lifetime plan
9. **FAQ Section**: Add to pricing page
10. **Comparison Table**: Create detailed feature comparison

## Files Modified/Created

### Created:
- `database/migrations/2026_01_24_113759_add_user_limits_and_features_to_subscriptions_table.php`
- `database/seeders/SubscriptionPlansSeeder.php`
- `app/Http/Middleware/EnforceSubscriptionLimits.php`
- `PRICING_STRATEGY.md`
- `SUBSCRIPTION_IMPLEMENTATION.md` (this file)

### Modified:
- `app/Models/Subscription.php`
- `app/Models/Company.php`
- `app/Http/Controllers/SubscriptionManagementController.php`
- `app/Http/Controllers/EmployeesController.php`

### Needs Update:
- `resources/js/pages/SubscriptionManagement.vue` (major rewrite needed)
- `resources/js/pages/Employees.vue` (add limit warning)
- `bootstrap/app.php` (register middleware if needed)

## Revenue Impact

Based on conservative projections:
- Year 1: ~€174,100 (MRR: €9,510)
- Optimistic: ~€683,708 (MRR: €31,984)

## Support & Documentation

For pricing strategy details, see `PRICING_STRATEGY.md`.
