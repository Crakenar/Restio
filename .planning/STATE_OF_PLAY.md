# Restio - State of Play (Updated)
**Last Updated:** 2026-01-18
**Status:** Pre-MVP Development - Core Workflows Needed

---

## Executive Summary

Restio is a vacation/leave management SaaS application built with Laravel 12, Inertia.js, and Vue 3. The application has a **complete, production-grade UI/UX system** with premium design, role-based dashboards, and team management. The foundation is solid, but key features need implementation or refinement.

**Current State:** Frontend 95% complete, Backend 40% complete. Focus needed on: **Notifications, Authorization, Balance Tracking, Request History, Manager-Team Assignment, and Settings.**

---

## ✅ Completed & Working Features

### **Authentication & Authorization (Foundation)**
- ✅ User authentication (Laravel Fortify)
- ✅ Two-factor authentication support
- ✅ User roles: Owner, Admin, Manager, Employee (RoleEnum)
- ✅ Email verification
- ✅ Password reset
- ✅ Company-scoped multi-tenancy (users isolated by company_id)
- ✅ Basic role checks in controllers

### **Subscription & Billing**
- ✅ Stripe integration for payments
- ✅ Subscription plans (Starter, Pro, Enterprise)
- ✅ Onboarding flow with payment
- ✅ Company subscription management
- ✅ Active subscription middleware

### **Team Management**
- ✅ Teams table and model (replaced Department system)
- ✅ Team CRUD operations (create, edit, delete)
- ✅ User-to-team assignment (bulk assign/remove)
- ✅ Team Management UI page with beautiful cards
- ✅ Unassigned users tracking
- ✅ Users belong to ONE team

### **Vacation Request System (Partial)**
- ✅ VacationRequest model with proper relationships
- ✅ Database schema with indexes
- ✅ Request creation endpoint (POST /vacation-requests)
- ✅ Request editing (pending only)
- ✅ Request cancellation (DELETE)
- ✅ Approve endpoint (POST /vacation-requests/{id}/approve)
- ✅ Reject endpoint (POST /vacation-requests/{id}/reject)
- ✅ Status tracking (pending, approved, rejected)
- ✅ Request types (vacation, sick, personal, unpaid, wfh)
- ✅ Basic notification sending on state changes

### **UI/UX System (Production-Ready)**
- ✅ **Premium Design System**
  - Glass morphism aesthetic
  - Animated gradient backgrounds
  - Smooth transitions and micro-interactions
  - Light & dark mode support
  - Responsive design (mobile-friendly)
  - Custom color palettes per page theme

- ✅ **Navigation**
  - PremiumSidebar component (collapsible, role-based)
  - Active route highlighting
  - Tooltips when collapsed
  - User profile section with logout

- ✅ **Pages (All with PremiumSidebar)**
  - Dashboard (role-aware: Admin, Manager, Employee)
  - Requests page (vacation request management)
  - Calendar page (vacation calendar view)
  - Team page (team calendar & employee overview)
  - Employees page (employee CRUD, CSV import)
  - Team Management page
  - Settings pages (Profile, Password, 2FA, Appearance)

- ✅ **Component Library**
  - Reka UI components (Button, Card, Input, Select, Dialog, etc.)
  - Custom components (RequestsTable, TeamCalendar, VacationCalendar)
  - Loading states & animations

### **Data Models**
- ✅ User model (with team relationship)
- ✅ Company model (multi-tenant architecture)
- ✅ Team model
- ✅ VacationRequest model
- ✅ CompanySetting model
- ✅ Subscription models
- ✅ Notification model (exists but not fully utilized)

### **Developer Experience**
- ✅ TypeScript interfaces for props
- ✅ Enums (UserRole, VacationRequestStatus, VacationRequestType)
- ✅ Laravel Boost MCP integration
- ✅ Wayfinder for type-safe routes
- ✅ Pint for code formatting

---

## 🚧 Partially Complete / Needs Work

### 1. **Notifications System** ⚠️
**Status:** Model exists, basic sending implemented, but incomplete

**What's Missing:**
- [ ] Notification center UI component (bell icon with badge)
- [ ] Notification list/dropdown showing recent notifications
- [ ] Mark as read functionality
- [ ] Notification preferences (email vs in-app)
- [ ] Email templates for vacation request events
- [ ] Real-time notification badge updates
- [ ] Notification types (request_approved, request_rejected, request_submitted, etc.)

**Priority:** HIGH - Critical for user experience

### 2. **Authorization & Policies** ⚠️
**Status:** Basic role checks exist, but no formal policies

**What's Missing:**
- [ ] VacationRequestPolicy (view, create, update, delete, approve, reject)
- [ ] TeamPolicy (view, create, update, delete, assignUsers)
- [ ] UserPolicy (view, create, update, delete)
- [ ] CompanyPolicy (view settings, manage users)
- [ ] Manager can only approve requests from their own team
- [ ] Admin can approve any request in their company
- [ ] Employees can only see/edit their own requests
- [ ] Policy registration in AuthServiceProvider
- [ ] Middleware checks using policies

**Priority:** CRITICAL - Security risk without this

### 3. **Vacation Balance Tracking** ⚠️
**Status:** Basic data exists, but no enforcement or calculations

**What's Missing:**
- [ ] Calculate total days used per year (from approved requests)
- [ ] Calculate remaining balance (annual_days - used_days)
- [ ] Prevent request submission if exceeds balance
- [ ] Display accurate balance on dashboard
- [ ] Handle partial days (half-day requests)
- [ ] Balance reset mechanism (annual reset)
- [ ] Balance adjustment feature (admin can add/remove days)
- [ ] Carryover rules (unused days to next year?)
- [ ] Different balance types (vacation, sick, personal)

**Priority:** HIGH - Core business logic

### 4. **Request History & Filtering** ⚠️
**Status:** Basic request list exists, needs enhancement

**What's Missing:**
- [ ] Advanced filtering (by employee, date range, status, type)
- [ ] Search functionality (by employee name, reason)
- [ ] Sorting (by date, employee, status)
- [ ] Pagination (currently showing all requests)
- [ ] Export to CSV/Excel
- [ ] Export filtered results
- [ ] Request details view (expand row or modal)
- [ ] Audit log (who approved/rejected, when)

**Priority:** MEDIUM - Important for usability

### 5. **Manager-Team Assignment** ⚠️
**Status:** Users can be assigned to teams, but managers aren't designated

**What's Missing:**
- [ ] Assign manager(s) to teams (team.manager_id or pivot table)
- [ ] Manager can only see/approve their team's requests
- [ ] Dashboard filtering by managed team
- [ ] Manager dashboard shows only their team's pending requests
- [ ] Hierarchy system (manager reports to admin)
- [ ] Multiple managers per team support?
- [ ] Team calendar filtered by manager's team

**Priority:** HIGH - Critical for proper workflows

### 6. **Settings Pages** ⚠️
**Status:** Basic pages exist, need refinement and features

**What's Missing:**

**Company Settings:**
- [ ] Edit company name
- [ ] Configure annual vacation days
- [ ] Configure approval requirements (auto-approve or require manager)
- [ ] Set company holidays (public holidays don't count)
- [ ] Configure working days (5-day vs 6-day week)
- [ ] Configure fiscal year start date
- [ ] Company branding (logo upload, colors)
- [ ] Time zone settings

**User Profile Settings:**
- [ ] Edit profile information (name, email)
- [ ] Upload profile photo/avatar
- [ ] Change password (exists but needs testing)
- [ ] Email preferences (notification settings)
- [ ] Delete account option

**Admin Settings:**
- [ ] Manage company subscription (upgrade/downgrade)
- [ ] View billing history
- [ ] Manage payment method
- [ ] User roles management
- [ ] Company-wide settings (annual days, approval flow)

**Appearance Settings:**
- [ ] Theme toggle (light/dark) - exists but needs persistence
- [ ] Color scheme preferences
- [ ] Sidebar preferences (collapsed by default)

**Priority:** MEDIUM - Important for customization

---

## ❌ Missing for MVP

### **Critical - Core Functionality**

#### 1. **Notification System Implementation**
- Build notification center component (bell icon, badge count)
- Create notification dropdown/list
- Implement mark-as-read functionality
- Configure email notifications (Mailgun/SendGrid)
- Create email templates for all notification types:
  - Request submitted (to manager)
  - Request approved (to employee)
  - Request rejected (to employee)
  - Request cancelled (to manager)
- Add notification preferences to user settings

#### 2. **Authorization Policies**
- Create VacationRequestPolicy with methods:
  - `viewAny()` - Can view requests list
  - `view()` - Can view specific request
  - `create()` - Can create request (all employees)
  - `update()` - Can update own pending request
  - `delete()` - Can cancel own pending request
  - `approve()` - Can approve (managers for their team, admins for company)
  - `reject()` - Can reject (same as approve)
- Create TeamPolicy for team management
- Create UserPolicy for employee management
- Register policies in AuthServiceProvider
- Apply policies to all controller methods
- Test edge cases (cross-company, cross-team access)

#### 3. **Vacation Balance Tracking**
- Add `VacationBalance` service/trait
- Calculate used days: `approvedRequests()->whereYear('start_date', now())->sum('days')`
- Calculate remaining: `company_settings.annual_days - used_days`
- Add validation rule: `balance_available` (check before allowing submission)
- Display balance on dashboard (Days Remaining card)
- Add balance to employee table view (admin)
- Prevent over-booking with clear error message
- Add manual balance adjustment feature (admin only)

#### 4. **Request History & Export**
- Add filtering UI (select boxes for status, type, employee, date range)
- Implement backend filtering (query scopes)
- Add search functionality (search by employee name or reason)
- Add sorting (clickable column headers)
- Implement pagination (20 requests per page)
- Add CSV export button
- Create export service (Laravel Excel or custom CSV)
- Export filtered results only
- Include all request details in export

#### 5. **Manager-Team Assignment**
- Decide on architecture:
  - Option A: Add `manager_id` to teams table (one manager per team)
  - Option B: Create `team_managers` pivot table (multiple managers per team)
  - **Recommendation:** Option A for simplicity
- Add migration for manager assignment
- Update Team model with `manager` relationship
- Create UI for assigning managers to teams (Team Management page)
- Filter manager dashboard to show only their team's requests
- Update approval logic to check manager-team relationship
- Update TeamCalendar to show manager's team only

#### 6. **Settings Pages Overhaul**
- **Company Settings Page:**
  - Form to edit company name, annual days, approval settings
  - Upload company logo
  - Configure holidays (date picker, list of holidays)
  - Save/update functionality
  - Only accessible to owner/admin
- **User Profile Page:**
  - Form to edit name, email
  - Avatar upload component
  - Email preferences checkboxes
  - Save/update functionality
- **Admin Settings Page:**
  - View current subscription plan
  - Upgrade/downgrade buttons (redirect to Stripe portal)
  - View billing history table
  - Manage payment method link
- **Appearance Settings:**
  - Theme toggle (persist to database or localStorage)
  - Color scheme selector (optional)

---

### **Important - Security & Validation**

#### 7. **Request Validation Rules**
- No overlapping requests (same user, overlapping dates)
- Valid date range (start_date <= end_date)
- Dates not in the past
- Sufficient vacation balance
- Company annual days not exceeded
- No requests on weekends (configurable)
- No requests on company holidays

#### 8. **Authorization Enforcement**
- Protect all routes with policies
- Ensure company-scoped queries (where('company_id', auth()->user()->company_id))
- Rate limiting on sensitive endpoints (approve/reject)
- Input sanitization on all forms
- CSRF protection (Laravel default)

---

### **Important - User Experience**

#### 9. **Calendar Integration**
- Display real vacation requests on VacationCalendar
- Color coding by status (pending: yellow, approved: green, rejected: red)
- Click date to create new request (open modal)
- TeamCalendar shows all team members' approved requests
- Filter by team member (dropdown)
- Legend for status colors

#### 10. **Dashboard Data Accuracy**
- Replace any remaining mock data
- Calculate stats from real data:
  - Days Used (approved requests, current year)
  - Days Remaining (annual - used)
  - Pending Requests (count)
  - Pending Approvals (for managers, their team's pending count)
- Show upcoming time off (next 30 days, approved requests)
- Show recent activity (last 5 requests)

#### 11. **Error Handling & Feedback**
- User-friendly error messages (not raw Laravel errors)
- Form validation feedback (red borders, error text)
- Success toasts (request submitted, approved, etc.)
- Loading states during async operations
- Network error handling (retry, offline message)
- 404/403 error pages (custom, branded)

#### 12. **Loading States**
- Skeleton loaders for tables, cards
- Spinner on approve/reject buttons
- Loading overlay on form submission
- Optimistic UI updates (update UI before server confirms)

---

## 📊 Technical Debt

### **Code Quality**
- ⚠️ No comprehensive test coverage (unit/feature tests)
- ⚠️ Some TypeScript interfaces duplicated across files
- ⚠️ Mock data still in some components (Dashboard stats)
- ⚠️ Need to standardize form handling (Inertia `<Form>` vs `useForm`)

### **Database**
- ⚠️ Department table exists but unused (can be dropped)
- ⚠️ No database seeds for development/testing
- ⚠️ No factories for models (testing difficult)
- ⚠️ Need to add `manager_id` to teams table

### **Performance**
- ⚠️ No query optimization (N+1 potential in TeamCalendar, EmployeeTable)
- ⚠️ No caching layer
- ⚠️ Large eager loads in some controllers
- ⚠️ No database indexes on frequently queried columns (besides VacationRequest)

### **Documentation**
- ⚠️ No API documentation (if exposing API later)
- ⚠️ No user manual / help documentation
- ⚠️ No admin guide
- ⚠️ No deployment guide

---

## 🎯 Recommended Next Steps (Priority Order)

### **Phase 1: Authorization & Security (CRITICAL)**
**Estimated Time:** 2-3 days
**Priority:** BLOCKING - Must be done first

1. ✅ Create VacationRequestPolicy
   - Define all authorization methods
   - Test with different roles
   - Apply to all controller methods

2. ✅ Create TeamPolicy and UserPolicy
   - Protect team management
   - Protect employee management

3. ✅ Implement Manager-Team Assignment
   - Add manager_id to teams table
   - Create UI for assignment
   - Filter dashboards by managed team

4. ✅ Test Authorization Edge Cases
   - Cross-company access attempts
   - Cross-team access attempts
   - Unauthorized approvals

**Deliverable:** Secure authorization system, no security holes

---

### **Phase 2: Core Business Logic (HIGH PRIORITY)**
**Estimated Time:** 3-4 days
**Priority:** CRITICAL - Core features

5. ✅ Vacation Balance Tracking
   - Create balance calculation service
   - Add validation rules (prevent over-booking)
   - Display accurate balances on dashboard
   - Show balances in employee table

6. ✅ Request Validation
   - No overlapping requests
   - Valid date ranges
   - Sufficient balance
   - Past date prevention

7. ✅ Calendar Integration
   - Display real requests on calendar
   - Color coding by status
   - Click to create request
   - Team calendar filtering

**Deliverable:** Functional vacation request system with balance tracking

---

### **Phase 3: Notifications (HIGH PRIORITY)**
**Estimated Time:** 2-3 days
**Priority:** HIGH - Critical UX

8. ✅ Notification Center UI
   - Bell icon with badge count
   - Notification dropdown
   - Mark as read functionality

9. ✅ Email Notifications
   - Configure mail driver (Mailgun/SendGrid)
   - Create email templates (request_approved, request_rejected, etc.)
   - Test email delivery

10. ✅ Notification Preferences
    - User settings for email vs in-app
    - Frequency settings (immediate, daily digest)

**Deliverable:** Complete notification system (in-app + email)

---

### **Phase 4: Settings & Admin Features (MEDIUM PRIORITY)**
**Estimated Time:** 3-4 days
**Priority:** MEDIUM - Important for customization

11. ✅ Company Settings Page
    - Edit company details
    - Configure annual days
    - Configure holidays
    - Upload logo
    - Approval workflow settings

12. ✅ User Profile Page
    - Edit profile information
    - Avatar upload
    - Email preferences
    - Password change

13. ✅ Admin Settings Page
    - Subscription management
    - Billing history
    - Payment method management

14. ✅ Appearance Settings
    - Theme toggle (persist to DB)
    - Color scheme preferences

**Deliverable:** Complete settings system for all user types

---

### **Phase 5: Request History & Reporting (MEDIUM PRIORITY)**
**Estimated Time:** 2-3 days
**Priority:** MEDIUM - Important for admins

15. ✅ Advanced Filtering & Search
    - Filter by employee, status, type, date range
    - Search by name or reason
    - Sorting by columns

16. ✅ Pagination
    - Paginate request lists (20 per page)
    - Server-side pagination for performance

17. ✅ Export Functionality
    - CSV export of filtered results
    - Include all request details
    - Export button on Requests page

**Deliverable:** Comprehensive request history with export

---

### **Phase 6: Polish & UX (MEDIUM PRIORITY)**
**Estimated Time:** 2-3 days
**Priority:** MEDIUM - Important for user satisfaction

18. ✅ Error Handling
    - User-friendly error messages
    - Form validation feedback
    - Custom 404/403 pages

19. ✅ Loading States
    - Skeleton loaders
    - Button spinners
    - Loading overlays

20. ✅ Dashboard Data Accuracy
    - Replace all mock data
    - Real-time stats calculations
    - Upcoming time off list

21. ✅ Mobile Responsiveness
    - Test all pages on mobile
    - Mobile menu for sidebar
    - Touch-friendly interactions

**Deliverable:** Polished, production-ready UX

---

### **Phase 7: Testing & Performance (IMPORTANT)**
**Estimated Time:** 3-4 days
**Priority:** HIGH - Critical for production

22. ✅ Feature Tests
    - Test request submission flow
    - Test approval/rejection flow
    - Test balance calculations
    - Test authorization policies

23. ✅ Unit Tests
    - Test business logic (balance calculation, date validation)
    - Test model methods
    - Test scopes and queries

24. ✅ Performance Optimization
    - Identify and fix N+1 queries
    - Add database indexes
    - Implement caching (Redis)
    - Optimize large queries

25. ✅ Browser Testing
    - Test in Chrome, Firefox, Safari, Edge
    - Test mobile browsers
    - Test accessibility (keyboard nav, screen readers)

**Deliverable:** Tested, optimized application ready for production

---

## 📈 Metrics

### **Codebase Size**
- **Backend:** ~20 controllers, ~10 models, ~20 migrations
- **Frontend:** ~15 pages, ~30 components
- **Lines of Code:** ~18,000 (estimate)

### **Database Tables**
- `users` - ✅ Complete
- `companies` - ✅ Complete
- `teams` - ✅ Complete (needs manager_id)
- `vacation_requests` - ✅ Complete (workflow needs work)
- `company_settings` - ✅ Complete
- `subscriptions` - ✅ Complete
- `notifications` - ⚠️ Exists but underutilized
- `departments` - ❌ Unused (can be dropped)

### **Routes**
- **Authenticated:** ~20 routes
- **Public:** ~5 routes (login, register, etc.)
- **Settings:** ~6 routes (profile, password, 2FA, appearance)

---

## 🔐 Security Checklist

- ✅ Authentication required for all app routes
- ✅ Company-scoped data isolation (multi-tenant)
- ✅ CSRF protection (Laravel default)
- ✅ Password hashing (bcrypt)
- ✅ Email verification
- ✅ Two-factor authentication
- ⚠️ **MISSING:** Authorization policies (CRITICAL)
- ⚠️ **MISSING:** Rate limiting on sensitive actions
- ⚠️ **MISSING:** Input sanitization on all forms
- ⚠️ **MISSING:** Audit log for admin actions
- ⚠️ **MISSING:** Cross-company data access prevention (need policies)

---

## 🚀 Deployment Readiness

### **Required Before Production**
- [ ] Authorization policies implemented and tested
- [ ] All core workflows functional (request → approve → calendar)
- [ ] Email notifications configured
- [ ] Database backups configured
- [ ] Environment variables secured
- [ ] Error logging (Sentry/similar)
- [ ] SSL certificate
- [ ] Domain configured
- [ ] Feature tests passing
- [ ] Performance optimized

### **Infrastructure Needs**
- [ ] PostgreSQL database (production)
- [ ] Redis for queue/cache (recommended)
- [ ] Email service (SMTP/SendGrid/Mailgun)
- [ ] File storage (S3 for avatars/documents if added)
- [ ] CDN for assets (optional)
- [ ] Application monitoring (New Relic/Datadog/similar)

---

## 🎯 Success Criteria for MVP

**MVP is ready when:**
1. ✅ Employee can register/login
2. ✅ Employee can submit vacation request (exists, needs testing)
3. ❌ Manager can approve/reject requests (needs authorization + manager assignment)
4. ❌ Calendar shows approved time off (needs real data integration)
5. ❌ Email notifications on approval/rejection (needs configuration)
6. ✅ Multi-company isolation works
7. ❌ Authorization prevents unauthorized actions (CRITICAL - needs policies)
8. ✅ UI is polished and responsive
9. ❌ Balance tracking prevents over-booking (needs implementation)
10. ❌ Settings pages allow customization (needs overhaul)

**Current MVP Completion:** ~45%
- Frontend/Design: 95% complete ✅
- Backend/Logic: 45% complete ⚠️
- Integration: 30% complete ⚠️
- Security: 40% complete ⚠️

---

## 💭 Key Insights

### **What's Going Well**
- The frontend is beautiful and production-ready
- Multi-tenant architecture is solid from the start
- Team management system is comprehensive
- Type safety catching issues early
- Component reuse is high
- Subscription & billing fully functional

### **Main Challenges**
- **Authorization is critical and blocking** - Needs policies before launch
- **Manager-team assignment is unclear** - Need to define hierarchy
- **Balance tracking is complex** - Carryover, adjustments, different types
- **Notification system needs UI** - Backend exists but no frontend
- **Settings pages need enhancement** - Basic pages exist but missing features
- **Request history needs filters** - Currently showing all requests with no filters

### **Strategic Recommendations**
1. **Start with Phase 1 (Authorization)** - Blocking security issue, must be done first
2. **Then Phase 2 (Business Logic)** - Balance tracking is core functionality
3. **Then Phase 3 (Notifications)** - Critical for user experience
4. **Then Phases 4-6** - Settings, history, polish
5. **Finally Phase 7 (Testing)** - Ensure production readiness

**The UI is done. Now secure it, then make it work!** 🔐🚀

---

## 📞 Quick Reference

**Last Session:** 2026-01-18
**Last Action:** Updated STATE_OF_PLAY with detailed requirements
**Next Action:** Implement Authorization Policies (Phase 1)
**Estimated MVP Timeline:** 15-20 working days

**Blocking Issues:** Authorization policies (security risk)
**Known Bugs:** None critical
**Performance Issues:** None observed yet

---

## 🛠️ Development Notes

### **Key Files to Work On Next:**
1. `/app/Policies/VacationRequestPolicy.php` (create)
2. `/app/Policies/TeamPolicy.php` (create)
3. `/app/Policies/UserPolicy.php` (create)
4. `/app/Providers/AuthServiceProvider.php` (register policies)
5. `/database/migrations/xxxx_add_manager_id_to_teams_table.php` (create)
6. `/app/Services/VacationBalanceService.php` (create)
7. `/resources/js/components/NotificationCenter.vue` (create)
8. `/resources/js/pages/settings/CompanySettings.vue` (enhance)

### **Laravel Artisan Commands to Use:**
```bash
# Create policies
php artisan make:policy VacationRequestPolicy --model=VacationRequest
php artisan make:policy TeamPolicy --model=Team
php artisan make:policy UserPolicy --model=User

# Create migration
php artisan make:migration add_manager_id_to_teams_table

# Create service
php artisan make:class Services/VacationBalanceService

# Run tests
php artisan test --filter=VacationRequest
```

### **Environment Variables Needed:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@restio.com
MAIL_FROM_NAME="Restio"
```

---

**END OF STATE_OF_PLAY**
