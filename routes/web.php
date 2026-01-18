<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RequestsController;
use App\Http\Controllers\SubscriptionManagementController;
use App\Http\Controllers\TeamManagementController;
use App\Http\Controllers\TeamsController;
use App\Http\Controllers\VacationRequestController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('/onboarding', [\App\Http\Controllers\OnboardingController::class, 'show'])
    ->middleware(['auth', 'verified', \App\Http\Middleware\EnsureCompanyIsActive::class])
    ->name('onboarding');

Route::post('/onboarding/checkout', [\App\Http\Controllers\OnboardingController::class, 'createCheckoutSession'])
    ->middleware(['auth', 'verified', \App\Http\Middleware\EnsureCompanyIsActive::class])
    ->name('onboarding.checkout');

Route::get('/onboarding/complete', [\App\Http\Controllers\OnboardingController::class, 'complete'])
    ->middleware(['auth', 'verified', \App\Http\Middleware\EnsureCompanyIsActive::class])
    ->name('onboarding.complete');

Route::middleware(['auth', 'verified', \App\Http\Middleware\EnsureCompanyIsActive::class])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard-demo', fn () => Inertia::render('DashboardDemo'))->name('dashboard.demo');
    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar');
    Route::get('requests', [RequestsController::class, 'index'])->name('requests');
    Route::get('teams', [TeamsController::class, 'index'])->name('teams');
    Route::get('employees', [EmployeesController::class, 'index'])->name('employees');
    Route::post('employees', [EmployeesController::class, 'store'])->name('employees.store');
    Route::post('employees/import', [EmployeesController::class, 'importCsv'])->name('employees.import');

    // Team Management (Admin/Owner only)
    Route::get('team-management', [TeamManagementController::class, 'index'])->name('team-management');
    Route::post('team-management', [TeamManagementController::class, 'store'])->name('team-management.store');
    Route::patch('team-management/{team}', [TeamManagementController::class, 'update'])->name('team-management.update');
    Route::delete('team-management/{team}', [TeamManagementController::class, 'destroy'])->name('team-management.destroy');
    Route::post('team-management/{team}/assign-users', [TeamManagementController::class, 'assignUsers'])->name('team-management.assign-users');
    Route::delete('team-management/{team}/users/{user}', [TeamManagementController::class, 'removeUser'])->name('team-management.remove-user');

    // Vacation Requests
    Route::post('vacation-requests', [VacationRequestController::class, 'store'])->name('vacation-requests.store');
    Route::patch('vacation-requests/{vacationRequest}', [VacationRequestController::class, 'update'])->name('vacation-requests.update');
    Route::delete('vacation-requests/{vacationRequest}', [VacationRequestController::class, 'destroy'])->name('vacation-requests.destroy');

    // Sensitive actions with stricter rate limiting (20 requests per minute)
    Route::post('vacation-requests/{vacationRequest}/approve', [VacationRequestController::class, 'approve'])
        ->middleware('throttle:sensitive')
        ->name('vacation-requests.approve');
    Route::post('vacation-requests/{vacationRequest}/reject', [VacationRequestController::class, 'reject'])
        ->middleware('throttle:sensitive')
        ->name('vacation-requests.reject');

    // Notifications
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Subscription Management (Owner only)
    Route::get('subscription', [SubscriptionManagementController::class, 'index'])->name('subscription.index');
    Route::post('subscription/change', [SubscriptionManagementController::class, 'changePlan'])->name('subscription.change');
    Route::get('subscription/upgrade/complete', [SubscriptionManagementController::class, 'completeUpgrade'])->name('subscription.upgrade.complete');
    Route::post('subscription/cancel', [SubscriptionManagementController::class, 'cancelSubscription'])->name('subscription.cancel');
});

require __DIR__.'/settings.php';
