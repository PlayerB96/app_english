<?php

/**
 * Rutas protegidas (requieren sesión autenticada):
 * - POST /logout
 * - GET  /dashboard, /practice, /tracks (learner)
 *
 * Rutas con rol administrador (auth + middleware role:administrator):
 * - GET  /admin, /admin/users, /admin/tracks, /admin/reports
 *
 * Rate limiting:
 * - POST /login → throttle:login (5 intentos/minuto por IP)
 * - API futura → limiter "api" en AppServiceProvider
 */

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MockPageController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::middleware('role:learner')->group(function () {
        Route::get('/dashboard', [MockPageController::class, 'learnerDashboard'])
            ->name('dashboard');
        Route::get('/practice', [MockPageController::class, 'practice'])
            ->name('practice.index');
        Route::get('/tracks', [MockPageController::class, 'tracks'])
            ->name('tracks.index');
    });

    Route::middleware('role:administrator')->prefix('admin')->group(function () {
        Route::get('/', [MockPageController::class, 'adminDashboard'])
            ->name('admin.dashboard');
        Route::get('/users', [MockPageController::class, 'adminUsers'])
            ->name('admin.users');
        Route::get('/tracks', [MockPageController::class, 'adminTracks'])
            ->name('admin.tracks');
        Route::get('/reports', [MockPageController::class, 'adminReports'])
            ->name('admin.reports');
    });
});
