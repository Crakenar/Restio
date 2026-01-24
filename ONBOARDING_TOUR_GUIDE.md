# Onboarding Tour Implementation Guide

## Overview

The onboarding tour uses **Driver.js** to create an interactive, step-by-step guide for new users. It's fully translated (EN/FR), styled to match Restio's design, and tracks completion in localStorage.

## What Was Installed

- ✅ `driver.js` package
- ✅ `useOnboardingTour()` composable
- ✅ Custom CSS styling (`driver-custom.css`)
- ✅ Full English & French translations
- ✅ Completion tracking

## How to Implement in Dashboard

### Step 1: Add `data-tour` Attributes to Elements

Add `data-tour` attributes to the elements you want to highlight in the tour:

```vue
<script setup lang="ts">
import { onMounted } from 'vue';
import { useOnboardingTour } from '@/composables/useOnboardingTour';

const { startDashboardTour, shouldShowTour } = useOnboardingTour();

onMounted(() => {
    // Auto-start tour for first-time users
    if (shouldShowTour()) {
        // Optional: Add a small delay so the page is fully rendered
        setTimeout(() => {
            startDashboardTour();
        }, 500);
    }
});
</script>

<template>
    <div class="p-6">
        <!-- Welcome Section -->
        <div data-tour="welcome" class="mb-6">
            <h1 class="text-2xl font-bold">Welcome, {{ user.name }}!</h1>
        </div>

        <!-- Stats Cards -->
        <div data-tour="stats" class="grid grid-cols-3 gap-4 mb-6">
            <div class="rounded-lg bg-card p-4">
                <h3>Vacation Days</h3>
                <p class="text-2xl font-bold">{{ stats.remaining }}</p>
            </div>
            <!-- More stats... -->
        </div>

        <!-- Request Button -->
        <button
            data-tour="request-button"
            class="rounded-full bg-gradient-to-r from-orange-500 to-rose-500 px-6 py-3 text-white"
        >
            Request Time Off
        </button>

        <!-- Sidebar (assuming it's in your layout) -->
        <aside data-tour="sidebar" class="sidebar">
            <!-- Navigation items -->
        </aside>

        <!-- Calendar Section -->
        <div data-tour="calendar" class="mt-6">
            <h2>Upcoming Time Off</h2>
            <!-- Calendar component -->
        </div>

        <!-- Notifications -->
        <div data-tour="notifications">
            <NotificationPanel />
        </div>
    </div>
</template>
```

### Step 2: Add a "Restart Tour" Button (Optional)

Allow users to restart the tour from settings or a help menu:

```vue
<script setup lang="ts">
import { useOnboardingTour } from '@/composables/useOnboardingTour';
import { Button } from '@/components/ui/button';
import { HelpCircle } from 'lucide-vue-next';

const { startDashboardTour, resetTour } = useOnboardingTour();

const handleRestartTour = () => {
    resetTour(); // Clear the completion flag
    startDashboardTour(); // Start the tour
};
</script>

<template>
    <Button @click="handleRestartTour" variant="outline" size="sm">
        <HelpCircle class="mr-2 h-4 w-4" />
        {{ $t('tour.restart') }}
    </Button>
</template>
```

### Step 3: Customize Tour Steps (Optional)

You can create custom tours for different pages. Edit `resources/js/composables/useOnboardingTour.ts`:

```typescript
const employeesTourSteps: DriveStep[] = [
    {
        element: '[data-tour="employee-list"]',
        popover: {
            title: 'Employee Management',
            description: 'View and manage all your team members here.',
            side: 'bottom',
        },
    },
    {
        element: '[data-tour="add-employee-btn"]',
        popover: {
            title: 'Add New Employee',
            description: 'Click here to add a new team member to your organization.',
            side: 'left',
        },
    },
    // Add more steps...
];

const startEmployeesTour = () => {
    const driverObj = driver({
        ...driverConfig,
        steps: employeesTourSteps,
    });
    driverObj.drive();
};
```

## Tour Step Configuration

Each step has these properties:

```typescript
{
    element: '[data-tour="unique-id"]',  // CSS selector
    popover: {
        title: 'Step Title',
        description: 'Step description',
        side: 'top' | 'right' | 'bottom' | 'left',  // Position
        align: 'start' | 'center' | 'end',          // Alignment
    },
}
```

### Special Step (No Element)

For final messages without highlighting an element:

```typescript
{
    popover: {
        title: 'All Done!',
        description: 'You\'re ready to go!',
    },
}
```

## Advanced Features

### Conditional Steps

Show different steps based on user role or conditions:

```typescript
const tourSteps = [];

// Always show welcome
tourSteps.push({
    element: '[data-tour="welcome"]',
    popover: { title: 'Welcome!', description: '...' },
});

// Only show admin features if user is admin
if (user.role === 'admin') {
    tourSteps.push({
        element: '[data-tour="admin-panel"]',
        popover: { title: 'Admin Panel', description: '...' },
    });
}

const driverObj = driver({ ...driverConfig, steps: tourSteps });
driverObj.drive();
```

### Manual Tour Control

Start tour programmatically (e.g., from a button click):

```vue
<button @click="startDashboardTour">Take a Tour</button>
```

### Track Tour Completion in Backend (Optional)

Instead of localStorage, save to the database:

```typescript
import { router } from '@inertiajs/vue3';

const driverConfig: Config = {
    // ...
    onDestroyed: () => {
        // Save to backend
        router.post('/user/tour-completed', {
            tour_name: 'dashboard',
        });
    },
};
```

Add to your User model:

```php
// Migration
Schema::table('users', function (Blueprint $table) {
    $table->json('tours_completed')->nullable();
});

// Model
protected $casts = [
    'tours_completed' => 'array',
];

// Check in frontend
const hasSeenTour = (): boolean => {
    return $page.props.auth.user?.tours_completed?.includes('dashboard') ?? false;
};
```

## Tour Best Practices

1. **Keep it short**: 5-7 steps maximum
2. **Focus on key features**: Don't explain everything
3. **Allow skipping**: Users should be able to skip the tour
4. **Mobile-friendly**: Test on mobile devices
5. **Timing**: Start after page fully loads (500ms delay recommended)
6. **Don't repeat**: Track completion and don't annoy users

## Styling Customization

Edit `resources/css/driver-custom.css` to change:

- Colors (currently uses Restio's orange/rose gradient)
- Border radius
- Font sizes
- Button styles
- Animations

## Translation Keys

Add new tour content in `resources/js/locales/en.json` and `fr.json`:

```json
{
  "tour": {
    "myNewTour": {
      "step1": {
        "title": "Step 1 Title",
        "description": "Step 1 Description"
      }
    }
  }
}
```

## Testing the Tour

1. **Clear localStorage** to test first-time experience:
   ```javascript
   localStorage.removeItem('onboarding_tour_completed');
   ```

2. **Refresh the page** - tour should auto-start

3. **Test language switching** - tour should work in both EN and FR

4. **Test on mobile** - ensure buttons and layout work

## Troubleshooting

### Tour Doesn't Start

- Check that `data-tour` attributes are present
- Verify elements are visible when tour starts
- Check browser console for errors
- Make sure Driver.js CSS is imported

### Elements Not Highlighting

- Ensure `data-tour` selector matches
- Check z-index of elements
- Try increasing `stagePadding` in config

### Translations Not Working

- Verify translation keys exist in both `en.json` and `fr.json`
- Check that `useI18n()` is imported
- Rebuild frontend: `npm run build`

## Example: Complete Dashboard Integration

```vue
<script setup lang="ts">
import { onMounted } from 'vue';
import { useOnboardingTour } from '@/composables/useOnboardingTour';
import { usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const page = usePage();
const user = computed(() => page.props.auth.user);

const { startDashboardTour, shouldShowTour, resetTour } = useOnboardingTour();

onMounted(() => {
    // Auto-start for new users
    if (shouldShowTour()) {
        setTimeout(() => {
            startDashboardTour();
        }, 800); // Wait for animations
    }
});
</script>

<template>
    <div class="dashboard">
        <!-- Restart Tour Button in Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 data-tour="welcome">{{ $t('dashboard.welcome', { name: user.name }) }}</h1>
            <Button @click="startDashboardTour" variant="ghost" size="sm">
                {{ $t('tour.restart') }}
            </Button>
        </div>

        <!-- Rest of dashboard with data-tour attributes... -->
    </div>
</template>
```

## Resources

- Driver.js Documentation: https://driverjs.com
- Custom styling: `resources/css/driver-custom.css`
- Composable: `resources/js/composables/useOnboardingTour.ts`
- Translations: `resources/js/locales/{en,fr}.json`
