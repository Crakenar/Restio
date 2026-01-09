# Codebase Concerns

**Analysis Date:** 2026-01-09

## Tech Debt

**User Registration Missing company_id:**
- Issue: `CreateNewUser` action creates users without assigning `company_id`
- File: `app/Actions/Fortify/CreateNewUser.php` (lines 33-37)
- Why: Multi-tenant fields added after initial Fortify setup
- Impact: User registration fails with foreign key constraint error
- Fix approach: Add company selection/creation during registration, or auto-assign default company

**Missing Approver Relationship on VacationRequest:**
- Issue: Model has `approved_by` foreign key but no `BelongsTo` relationship method
- File: `app/Models/VacationRequest.php`
- Why: Relationship was not defined when model was created
- Impact: Cannot eager-load approver, N+1 queries when displaying approver info
- Fix approach: Add `public function approver(): BelongsTo` relationship

**Hardcoded Mock Data in Dashboard:**
- Issue: 60+ lines of fake vacation requests and employee data
- File: `resources/js/pages/Dashboard.vue` (lines 42-102)
- Why: Frontend developed before backend API endpoints
- Impact: Dashboard shows fake data, not real database content
- Fix approach: Create API endpoints, replace mock data with Inertia props

## Known Bugs

**Registration Test Will Fail:**
- Symptoms: Test creates user without required `company_id`
- Trigger: Run `php artisan test --filter=RegistrationTest`
- File: `tests/Feature/Auth/RegistrationTest.php` (lines 21-22)
- Workaround: None currently
- Root cause: Test doesn't provide company_id, but migration requires it
- Fix: Update test to create company first, or make registration handle company creation

**Dashboard Filters Use Hardcoded User:**
- Symptoms: Dashboard always filters by 'John Doe' regardless of logged-in user
- Trigger: Log in as any user, view dashboard
- File: `resources/js/pages/Dashboard.vue` (lines 135, 140)
- Workaround: None
- Root cause: Hardcoded filter instead of using `auth.user` prop
- Fix: Replace hardcoded name with auth user data

## Security Considerations

**No Authorization Policies:**
- Risk: Users could potentially modify other users' data
- Files: All controllers in `app/Http/Controllers/`
- Current mitigation: Authentication middleware only
- Recommendations: Create Policy classes for User, VacationRequest, Company models

**Console.log in Production Code:**
- Risk: May leak sensitive information in browser console
- Files: `resources/js/pages/Dashboard.vue` (line 129), possibly others
- Current mitigation: None
- Recommendations: Remove all console.log statements, use proper logging in development only

**Multi-Tenant Data Not Isolated:**
- Risk: Users from different companies could potentially access each other's data
- Files: All model queries
- Current mitigation: None (no global scopes)
- Recommendations: Add company-based global scope to models, or middleware to set company context

## Performance Bottlenecks

**No N+1 Prevention for VacationRequest->Approver:**
- Problem: Fetching vacation requests doesn't include approver relationship
- File: `app/Models/VacationRequest.php`
- Measurement: Not yet measured (relationship doesn't exist)
- Cause: Missing `approver` relationship method
- Improvement path: Add relationship, use eager loading in queries

## Fragile Areas

**Fortify User Creation:**
- File: `app/Actions/Fortify/CreateNewUser.php`
- Why fragile: Coupled to Fortify package, requires company_id but doesn't handle it
- Common failures: Foreign key constraint errors on registration
- Safe modification: Add company handling with tests
- Test coverage: `RegistrationTest.php` exists but will fail

**Dashboard Component:**
- File: `resources/js/pages/Dashboard.vue`
- Why fragile: Large file (341 lines), mixes mock data with real logic
- Common failures: Changes to mock data structure break component
- Safe modification: Extract sub-components, replace mock with API
- Test coverage: Only `DashboardTest.php` for basic access

## Scaling Limits

**Database Sessions/Cache:**
- Current capacity: Single database handles all session and cache operations
- Limit: Database connection pool exhaustion under high load
- Symptoms at limit: Slow page loads, session errors
- Scaling path: Move to Redis for sessions and cache (already configured in `config/database.php`)

## Dependencies at Risk

**Not Detected:**
- All dependencies appear actively maintained
- Laravel 12, Vue 3, Tailwind 4 are current versions

## Missing Critical Features

**API Routes for Vacation Requests:**
- Problem: No backend endpoints for vacation CRUD operations
- Files: `routes/web.php` (missing routes)
- Current workaround: Dashboard shows mock data
- Blocks: Full vacation request workflow (create, approve, reject)
- Implementation complexity: Medium (controllers, requests, policies needed)

**Company Selection During Registration:**
- Problem: Users cannot register because company_id is required
- Files: `app/Actions/Fortify/CreateNewUser.php`
- Current workaround: None (registration broken)
- Blocks: New user onboarding
- Implementation complexity: Medium (registration flow, company creation/selection)

**Unused CompanySetting Fields:**
- Problem: `annual_days` and `approval_required` fields exist but are never used
- File: `app/Models/CompanySetting.php`
- Current workaround: N/A
- Blocks: Nothing currently
- Implementation complexity: Low (integrate into vacation request logic)

## Test Coverage Gaps

**Vacation Request Features:**
- What's not tested: VacationRequest model, CRUD operations, approval workflow
- Risk: Core business logic untested
- Priority: High
- Difficulty to test: Low (standard feature tests)

**Multi-Tenant Data Isolation:**
- What's not tested: Company-based data access
- Risk: Users might see other companies' data
- Priority: High
- Difficulty to test: Medium (need to create multi-company scenarios)

**Company Model and Settings:**
- What's not tested: Company creation, settings management
- Risk: Tenant configuration bugs
- Priority: Medium
- Difficulty to test: Low (standard model tests)

---

*Concerns audit: 2026-01-09*
*Update as issues are fixed or new ones discovered*
