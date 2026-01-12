<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\RequestsController;
use App\Http\Controllers\TeamsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('requests', [RequestsController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('requests');

Route::get('teams', [TeamsController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('teams');

Route::get('employees', [EmployeesController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('employees');

Route::post('employees', [EmployeesController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('employees.store');

Route::post('employees/import', [EmployeesController::class, 'importCsv'])
    ->middleware(['auth', 'verified'])
    ->name('employees.import');

require __DIR__.'/settings.php';
