# Restio

## What This Is

A vacation/leave management app for teams and small businesses. Employees request time off, managers approve or deny requests, and managers/HR get visibility into who's on vacation across their teams. Multi-tenant architecture where each company operates in isolation.

## Core Value

Employees can request time off and managers can approve it, with clear visibility into team availability — the complete request-to-approval workflow must work smoothly.

## Requirements

### Validated

- ✓ User authentication (login, logout, password reset) — existing
- ✓ Two-factor authentication support — existing
- ✓ User profile and settings management — existing
- ✓ Company/team data model structure — existing
- ✓ VacationRequest model with statuses and types — existing
- ✓ User roles (Admin, Manager, Employee) — existing
- ✓ Basic page structure (Dashboard, Team, Requests) — existing
- ✓ Light/dark theme support — existing

### Active

- [ ] Fix user registration (company_id requirement)
- [ ] Vacation request submission (employees create requests)
- [ ] Approval workflow (managers approve/deny requests)
- [ ] Team calendar view (see who's off when)
- [ ] Manager dashboard (pending requests, team overview)
- [ ] HR/Admin visibility across all teams
- [ ] Multi-tenant data isolation (company-scoped queries)
- [ ] Authorization policies (who can do what)
- [ ] Replace mock dashboard data with real API

### Out of Scope

- Balance tracking — no accrual rules, carryover calculations, or remaining days tracking for v1
- Integrations — no Slack notifications, calendar sync (Google/Outlook), or payroll system connections
- Advanced policies — no blackout dates, multi-level approval chains, or department-specific rules
- Mobile app — web-only for v1, responsive design sufficient
- Reporting/analytics — no export features or usage reports

## Context

**Existing Foundation:**
- Laravel 12 + Inertia.js + Vue 3 SPA architecture
- Fortify authentication with 2FA support
- Wayfinder for type-safe frontend-backend routing
- Reka UI component library with Tailwind CSS v4
- Database models exist: User, Company, VacationRequest, CompanySetting
- Enums defined: UserRole, VacationRequestStatus, VacationRequestType

**Known Issues to Address:**
- Registration broken: `CreateNewUser` doesn't handle `company_id` (foreign key failure)
- Dashboard uses 60+ lines of hardcoded mock data
- Missing `approver` relationship on VacationRequest model
- No authorization policies exist (security risk)
- No company-scoped global queries (multi-tenant leak risk)

**Codebase Documentation:**
- Full architecture and structure documented in `.planning/codebase/`
- 7 analysis documents covering stack, architecture, conventions, concerns

## Constraints

- **Multi-tenant**: Each company's data must be completely isolated — users only see their own company's data
- **Tech stack**: Must use existing Laravel/Inertia/Vue stack — no framework changes
- **Authentication**: Must preserve existing Fortify auth flows — enhance, don't replace

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Company-scoped global queries | Ensures multi-tenant isolation at model level | — Pending |
| Managers approve their team's requests | Simple hierarchy, no complex approval chains | — Pending |
| HR/Admin sees all company data | Role-based visibility without department complexity | — Pending |

---
*Last updated: 2026-01-09 after initialization*
