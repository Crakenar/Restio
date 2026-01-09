<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('requests', function () {
    return Inertia::render('Requests');
})->middleware(['auth', 'verified'])->name('requests');

Route::get('teams', function () {
    return Inertia::render('Team');
})->middleware(['auth', 'verified'])->name('teams');

require __DIR__.'/settings.php';
