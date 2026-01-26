<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CompaniesController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LogsController;
use App\Http\Controllers\Admin\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "admin" middleware group.
|
*/

// Admin Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');

// Protected Admin Routes
Route::middleware(['auth:admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

    // Users Management
    Route::get('/users', [UsersController::class, 'index'])->name('admin.users.index');
    Route::get('/users/{user}', [UsersController::class, 'show'])->name('admin.users.show');

    // Companies Management
    Route::get('/companies', [CompaniesController::class, 'index'])->name('admin.companies.index');
    Route::get('/companies/{company}', [CompaniesController::class, 'show'])->name('admin.companies.show');

    // Logs Viewer
    Route::get('/logs', [LogsController::class, 'index'])->name('admin.logs.index');
    Route::get('/logs/download', [LogsController::class, 'download'])->name('admin.logs.download');
    Route::post('/logs/clear', [LogsController::class, 'clear'])->name('admin.logs.clear');
});
