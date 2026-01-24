# Role-Based Onboarding Tours - Implementation Summary

## Overview
Complete implementation of role-based onboarding tours using Driver.js, with separate guided experiences for employees, managers, and admins.

## What Was Implemented

### 1. Tour Infrastructure (`/resources/js/composables/useOnboardingTour.ts`)
- **Three separate tour configurations:**
  - Employee tour: 6 steps (welcome, stats, request button, upcoming, sidebar, complete)
  - Manager tour: 6 steps (welcome, pending requests, approve/reject, team overview, sidebar, complete)
  - Admin tour: 7 steps (welcome, analytics, employee management, requests table, sidebar, settings, complete)

- **Key features:**
  - Role-based tour selection with `startTourForRole(role)`
  - Individual completion tracking per role in localStorage
  - Auto-detection of first-time users with `shouldShowTour(role)`
  - Reset functionality for re-watching tours

### 2. Tour Button Component (`/resources/js/components/TourButton.vue`)
- Reusable button component that accepts a `role` prop
- Integrated with tooltip showing "Restart Tour" translation
- Uses HelpCircle icon
- Placed in the sidebar for easy access

### 3. Dashboard Integration

#### Employee Dashboard (`HybridDashboard.vue`)
- ✅ `data-tour="welcome"` → Welcome card
- ✅ `data-tour="stats"` → Vacation balance stats
- ✅ `data-tour="request-button"` → Request Time Off button
- ✅ `data-tour="upcoming"` → Upcoming Time Off section

#### Manager Dashboard (`ManagerDashboard.vue`)
- ✅ `data-tour="welcome"` → Header metrics section
- ✅ `data-tour="pending-requests"` → Pending requests tab
- ✅ `data-tour="approve-reject"` → Approve/reject buttons
- ✅ `data-tour="team-overview"` → Team calendar tab

#### Admin Dashboard (`AdminDashboard.vue`)
- ✅ `data-tour="welcome"` → Admin dashboard header
- ✅ `data-tour="analytics"` → Analytics grid (department breakdown, request types)
- ✅ `data-tour="employee-management"` → Employee management tab
- ✅ `data-tour="requests-table"` → All requests table tab

### 4. Sidebar Integration (`PremiumSidebar.vue`)
- Added TourButton with role detection
- ✅ `data-tour="sidebar"` → Navigation sidebar (used in all three tours)
- ✅ `data-tour="settings"` → Settings link (used in admin tour)

### 5. Auto-Start on First Login (`Dashboard.vue`)
- Tours automatically start 800ms after page load for first-time users
- Role detection logic maps UserRole enum to tour role type
- Only shows tour once per role (tracked in localStorage)

### 6. Translations (English & French)
Complete translations in both languages for all tour steps:
- `/resources/js/locales/en.json`
- `/resources/js/locales/fr.json`

Each tour step includes:
- Title
- Description explaining the feature

## How to Use

### For Users
1. **First login:** Tour starts automatically based on your role
2. **Restart tour:** Click the help button (?) in the sidebar
3. **Skip tour:** Click "Skip Tour" during any step

### For Developers
```typescript
// Import the composable
import { useOnboardingTour } from '@/composables/useOnboardingTour';

// Use in component
const { startTourForRole, shouldShowTour } = useOnboardingTour();

// Start specific tour
startTourForRole('employee'); // or 'manager' or 'admin'

// Check if user has seen tour
if (shouldShowTour('employee')) {
  startTourForRole('employee');
}
```

## Tour Steps Breakdown

### Employee Tour (6 steps)
1. **Welcome:** Introduction to Restio
2. **Stats:** Vacation balance explanation
3. **Request Button:** How to submit requests
4. **Upcoming:** View approved time off
5. **Sidebar:** Quick navigation guide
6. **Complete:** Encouragement message

### Manager Tour (6 steps)
1. **Welcome:** Manager dashboard introduction
2. **Pending Requests:** Review pending approvals
3. **Approve/Reject:** Action buttons explanation
4. **Team Overview:** Monitor team absences
5. **Sidebar:** Manager tools access
6. **Complete:** Ready to manage message

### Admin Tour (7 steps)
1. **Welcome:** Admin dashboard introduction
2. **Analytics:** Company-wide statistics
3. **Employee Management:** Manage all employees
4. **Requests Table:** Advanced filtering
5. **Sidebar:** Admin navigation
6. **Settings:** Company configuration
7. **Complete:** Admin powers unlocked message

## Custom Styling
- Located in `/resources/css/driver-custom.css`
- Matches Restio brand colors (orange/rose gradient)
- Includes glassmorphism effects
- Dark mode support
- Mobile responsive

## Technical Notes
- Tours are non-blocking and can be dismissed at any time
- Each role's tour completion is tracked separately in localStorage
- Tours use the i18n system for multi-language support
- Driver.js version: ^1.4.2
- Custom CSS provides brand-consistent styling

## Files Modified/Created
1. `/resources/js/composables/useOnboardingTour.ts` (refactored for roles)
2. `/resources/js/components/TourButton.vue` (added role prop)
3. `/resources/js/components/PremiumSidebar.vue` (added TourButton & data-tour attributes)
4. `/resources/js/pages/Dashboard.vue` (added auto-start logic)
5. `/resources/js/components/dashboards/HybridDashboard.vue` (added data-tour attributes)
6. `/resources/js/components/dashboards/ManagerDashboard.vue` (added data-tour attributes)
7. `/resources/js/components/dashboards/AdminDashboard.vue` (added data-tour attributes)
8. `/resources/js/locales/en.json` (added tour translations)
9. `/resources/js/locales/fr.json` (added tour translations)

## Status
✅ **Complete and tested** - Frontend builds successfully with all tours implemented
