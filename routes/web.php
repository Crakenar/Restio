<?php

use App\Http\Controllers\DashboardController;
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

require __DIR__.'/settings.php';
