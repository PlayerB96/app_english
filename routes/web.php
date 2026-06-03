<?php

/**
 * Rutas protegidas (requieren sesión autenticada):
 * - POST /logout
 * - GET  /dashboard
 *
 * Rutas con rol administrador (auth + middleware role:administrator):
 * - GET  /admin
 *
 * Rate limiting:
 * - POST /login → throttle:login (5 intentos/minuto por IP)
 * - API futura → limiter "api" en AppServiceProvider
 */

use App\Http\Controllers\AuthController;
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
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    Route::middleware('role:administrator')->group(function () {
        Route::get('/admin', function () {
            return Inertia::render('Dashboard', [
                'section' => 'admin',
            ]);
        })->name('admin.panel');
    });
});
