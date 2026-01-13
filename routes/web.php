<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\RequestsController;
use App\Http\Controllers\SubscriptionManagementController;
use App\Http\Controllers\TeamsController;
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
    Route::get('requests', [RequestsController::class, 'index'])->name('requests');
    Route::get('teams', [TeamsController::class, 'index'])->name('teams');
    Route::get('employees', [EmployeesController::class, 'index'])->name('employees');
    Route::post('employees', [EmployeesController::class, 'store'])->name('employees.store');
    Route::post('employees/import', [EmployeesController::class, 'importCsv'])->name('employees.import');

    // Subscription Management (Owner only)
    Route::get('subscription', [SubscriptionManagementController::class, 'index'])->name('subscription.index');
    Route::post('subscription/change', [SubscriptionManagementController::class, 'changePlan'])->name('subscription.change');
    Route::get('subscription/upgrade/complete', [SubscriptionManagementController::class, 'completeUpgrade'])->name('subscription.upgrade.complete');
    Route::post('subscription/cancel', [SubscriptionManagementController::class, 'cancelSubscription'])->name('subscription.cancel');
});

require __DIR__.'/settings.php';
