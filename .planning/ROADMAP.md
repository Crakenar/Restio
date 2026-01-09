# Roadmap: Restio

## Overview

Build a complete vacation/leave management workflow from the existing foundation. Start by fixing critical issues (registration, multi-tenant isolation), then build the core request-and-approve flow, add calendar visibility, and polish with role-specific dashboards.

## Domain Expertise

None

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

- [ ] **Phase 1: Foundation Fixes** - Fix registration, multi-tenant isolation, authorization policies
- [ ] **Phase 2: Request Submission** - Employees can create vacation requests
- [ ] **Phase 3: Approval Workflow** - Managers can view, approve, and deny requests
- [ ] **Phase 4: Team Calendar** - Visual calendar showing who's off when
- [ ] **Phase 5: Manager Dashboard** - Pending requests, team overview, quick actions
- [ ] **Phase 6: Admin/HR Visibility** - Cross-team visibility for admins and HR
- [ ] **Phase 7: Polish & Integration** - Replace mock data, wire up all components

## Phase Details

### Phase 1: Foundation Fixes
**Goal**: Fix blocking issues — registration works, data is isolated per company, authorization in place
**Depends on**: Nothing (first phase)
**Research**: Unlikely (Laravel global scopes and policies are established patterns)
**Plans**: TBD

Key work:
- Fix CreateNewUser to handle company_id
- Add company-scoped global scope to models
- Create authorization policies for VacationRequest
- Add approver relationship to VacationRequest model

### Phase 2: Request Submission
**Goal**: Employees can submit vacation requests with proper validation
**Depends on**: Phase 1
**Research**: Unlikely (CRUD operations using existing patterns)
**Plans**: TBD

Key work:
- Request creation form (date range, type, notes)
- Validation rules (no overlapping requests, valid dates)
- Controller and routes for request CRUD
- Employee's "My Requests" view

### Phase 3: Approval Workflow
**Goal**: Managers can review and approve/deny requests from their team
**Depends on**: Phase 2
**Research**: Unlikely (state transitions, business logic)
**Plans**: TBD

Key work:
- Pending requests list for managers
- Approve/deny actions with optional notes
- Status transitions (pending → approved/rejected)
- Notifications or visual feedback on status change

### Phase 4: Team Calendar
**Goal**: Visual calendar showing team availability at a glance
**Depends on**: Phase 3
**Research**: Likely (calendar visualization approach)
**Research topics**: Existing TeamCalendar.vue component capabilities, date-fns patterns, monthly/weekly views
**Plans**: TBD

Key work:
- Calendar component showing approved time off
- Filter by team/department
- Visual indicators for pending vs approved
- Month navigation

### Phase 5: Manager Dashboard
**Goal**: Managers see pending requests and team overview in one place
**Depends on**: Phase 4
**Research**: Unlikely (aggregating existing data)
**Plans**: TBD

Key work:
- Pending requests count and list
- Team availability summary
- Quick approve/deny actions
- Upcoming time off preview

### Phase 6: Admin/HR Visibility
**Goal**: Admins and HR can see all teams' data across the company
**Depends on**: Phase 5
**Research**: Unlikely (role-based query filtering)
**Plans**: TBD

Key work:
- Company-wide calendar view
- All pending requests across teams
- User/team management views
- Override capabilities for admins

### Phase 7: Polish & Integration
**Goal**: Remove mock data, ensure all components work together seamlessly
**Depends on**: Phase 6
**Research**: Unlikely (integration work)
**Plans**: TBD

Key work:
- Replace all mock/hardcoded data with real API calls
- End-to-end flow testing
- Error handling and edge cases
- UI/UX polish

## Progress

**Execution Order:**
Phases execute in numeric order: 1 → 2 → 3 → 4 → 5 → 6 → 7

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Foundation Fixes | 0/TBD | Not started | - |
| 2. Request Submission | 0/TBD | Not started | - |
| 3. Approval Workflow | 0/TBD | Not started | - |
| 4. Team Calendar | 0/TBD | Not started | - |
| 5. Manager Dashboard | 0/TBD | Not started | - |
| 6. Admin/HR Visibility | 0/TBD | Not started | - |
| 7. Polish & Integration | 0/TBD | Not started | - |
