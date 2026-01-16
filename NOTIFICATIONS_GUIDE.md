# Notification System Documentation

## Overview

A beautiful, premium notification UI system with glass morphism aesthetics that seamlessly integrates with your existing design.

## Features

### Visual Design
- **Liquid Glass Aesthetics**: Frosted glass panels with animated gradient overlays
- **Color-Coded Notifications**:
  - 🟢 Approved: Emerald/Teal gradient
  - 🔴 Rejected: Red/Rose gradient
  - 🔵 Submitted: Blue/Indigo gradient
- **Smooth Animations**: Spring physics, staggered reveals, hover effects
- **Pulsing Badge**: Animated notification count on bell icon
- **Responsive Design**: Works beautifully on all screen sizes

### Functionality
- **Real-time Updates**: Notifications appear instantly when vacation requests are submitted/approved/rejected
- **Smart Filtering**: Shows unread notifications first, then earlier notifications
- **One-Click Actions**: Click any notification to navigate to requests page and mark as read
- **Bulk Actions**: "Mark all as read" button for clearing multiple notifications
- **Auto-Refresh**: Notifications update automatically via Inertia

## Components Created

### 1. NotificationBell.vue
Location: `resources/js/components/NotificationBell.vue`

The bell icon in the sidebar with:
- Animated badge showing unread count
- Pulse glow effect for new notifications
- Wiggle animation when clicked
- Tooltip support when sidebar is collapsed

### 2. NotificationPanel.vue
Location: `resources/js/components/NotificationPanel.vue`

The dropdown panel displaying notifications:
- Slides down with spring physics
- Backdrop blur overlay
- Grouped notifications (New vs Earlier)
- Empty state with friendly message
- Smooth scroll with custom scrollbar

### 3. NotificationController.php
Location: `app/Http/Controllers/NotificationController.php`

Backend controller with routes:
- `POST /notifications/{id}/read` - Mark single notification as read
- `POST /notifications/read-all` - Mark all notifications as read

## How It Works

### When a Vacation Request is Submitted:
1. Employee submits request via `VacationRequestController::store()`
2. System creates request in database
3. **Notification sent** to all managers/admins/owners in the company
4. Notification stored in database with type `VacationRequestSubmitted`
5. Bell icon shows updated unread count
6. Panel displays new notification with blue gradient

### When a Request is Approved:
1. Manager/Admin clicks approve on `VacationRequestController::approve()`
2. Request status updated to "approved"
3. **Notification sent** to the employee
4. Notification type: `VacationRequestApproved` with green gradient
5. Employee sees "Your time off request has been approved"

### When a Request is Rejected:
1. Manager/Admin clicks reject on `VacationRequestController::reject()`
2. Request status updated to "rejected"
3. **Notification sent** to the employee with rejection reason
4. Notification type: `VacationRequestRejected` with red gradient
5. Employee sees reason (if provided)

## Data Flow

```
User Action (Submit/Approve/Reject)
    ↓
VacationRequestController
    ↓
Create Notification (Laravel Notifications)
    ↓
Store in database (notifications table)
    ↓
HandleInertiaRequests Middleware
    ↓
Share with all pages via Inertia
    ↓
PremiumSidebar receives notifications
    ↓
NotificationBell displays count
    ↓
NotificationPanel shows details
```

## Customization

### Changing Notification Colors

Edit `NotificationPanel.vue` in the `getNotificationStyle()` function:

```typescript
if (notifType.includes('approved')) {
    return {
        gradient: 'from-emerald-500 to-teal-600', // Change these
        iconColor: 'text-emerald-600 dark:text-emerald-400',
    };
}
```

### Adding New Notification Types

1. Create new notification class:
```bash
php artisan make:notification NewNotificationType
```

2. Implement with database channel:
```php
public function via(object $notifiable): array
{
    return ['database'];
}

public function toArray(object $notifiable): array
{
    return [
        'message' => 'Your custom message',
        'vacation_request_id' => $this->vacationRequest->id,
        // ... more data
    ];
}
```

3. Send notification:
```php
$user->notify(new NewNotificationType($data));
```

4. Add color scheme in `NotificationPanel.vue` `getNotificationStyle()`

### Changing Animation Timings

Edit `NotificationPanel.vue` styles:

```css
.slide-fade-enter-active {
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); /* Adjust timing */
}
```

## Testing

The notification system works with your existing test suite. All notification tests pass:

```bash
php artisan test --filter=VacationRequestNotificationTest
```

Tests cover:
- ✅ Managers/admins/owners receive submission notifications
- ✅ Only same-company users receive notifications
- ✅ Employees receive approval notifications
- ✅ Employees receive rejection notifications
- ✅ Notification data accuracy

## Next Steps (Optional Enhancements)

1. **In-App Notification Sound**: Add subtle sound effect when new notification arrives
2. **Notification Preferences**: Let users customize which notifications they receive
3. **Desktop Notifications**: Browser push notifications (requires service worker)
4. **Email Notifications**: Re-enable email channel if desired
5. **Notification History Page**: Dedicated page showing all notifications with filtering
6. **Rich Notifications**: Add avatars, action buttons within notifications

## Browser Compatibility

- ✅ Chrome/Edge (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

Uses modern CSS features:
- Backdrop blur (fallback to solid background on older browsers)
- CSS animations
- Flexbox/Grid

---

**Created**: 2026-01-15
**Status**: Production Ready ✨
