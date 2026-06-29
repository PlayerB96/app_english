<?php

/**
 * Rutas protegidas (requieren sesión autenticada):
 * - POST /logout
 * - GET  /dashboard, /practice, /tracks, /world (learner)
 *
 * Rutas con rol administrador (auth + middleware role:administrator):
 * - GET  /admin, /admin/users, /admin/tracks, /admin/reports
 *
 * Rate limiting:
 * - POST /login → throttle:login (5 intentos/minuto por IP)
 * - API futura → limiter "api" en AppServiceProvider
 */

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\LevelProgressController;
use App\Http\Controllers\MockPageController;
use App\Http\Controllers\PowerShopController;
use App\Http\Controllers\WorldController;
use App\Http\Controllers\WorldLevelController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canonicalUrl' => config('app.url'),
    ]);
})->name('home');

Route::get('/robots.txt', function () {
    $sitemap = rtrim(config('app.url'), '/').'/sitemap.xml';

    return response(
        "User-agent: *\nAllow: /\n\nSitemap: {$sitemap}\n",
        200,
        ['Content-Type' => 'text/plain; charset=UTF-8'],
    );
})->name('robots');

Route::get('/legal/terminos', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/legal/privacidad', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/legal/devoluciones', [LegalController::class, 'refunds'])->name('legal.refunds');
Route::get('/legal/proveedor', [LegalController::class, 'notice'])->name('legal.notice');
Route::get('/legal/reclamaciones', [LegalController::class, 'complaints'])->name('legal.complaints');
Route::post('/legal/reclamaciones', [LegalController::class, 'storeComplaint'])
    ->middleware('throttle:6,1')
    ->name('legal.complaints.store');

Route::get('/sitemap.xml', function () {
    $base = rtrim(config('app.url'), '/');
    $urls = [
        ['loc' => $base.'/', 'changefreq' => 'weekly', 'priority' => '1.0'],
        ['loc' => $base.'/login', 'changefreq' => 'monthly', 'priority' => '0.8'],
        ['loc' => $base.'/register', 'changefreq' => 'monthly', 'priority' => '0.8'],
        ['loc' => $base.'/legal/terminos', 'changefreq' => 'monthly', 'priority' => '0.5'],
        ['loc' => $base.'/legal/privacidad', 'changefreq' => 'monthly', 'priority' => '0.5'],
        ['loc' => $base.'/legal/devoluciones', 'changefreq' => 'monthly', 'priority' => '0.5'],
        ['loc' => $base.'/legal/proveedor', 'changefreq' => 'monthly', 'priority' => '0.5'],
        ['loc' => $base.'/legal/reclamaciones', 'changefreq' => 'monthly', 'priority' => '0.6'],
    ];

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    foreach ($urls as $url) {
        $xml .= '<url>'
            .'<loc>'.e($url['loc']).'</loc>'
            .'<changefreq>'.$url['changefreq'].'</changefreq>'
            .'<priority>'.$url['priority'].'</priority>'
            .'</url>';
    }

    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml']);
})->name('sitemap');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');

    Route::get('/register', [AuthController::class, 'registerCreate'])->name('register');
    Route::post('/register', [AuthController::class, 'registerStore'])
        ->middleware('throttle:login')
        ->name('register.store');
});

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::middleware(['verified', 'role:learner'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');
        Route::get('/practice', [MockPageController::class, 'practice'])
            ->name('practice.index');
        Route::get('/tracks', [MockPageController::class, 'tracks'])
            ->name('tracks.index');
        Route::get('/world', [WorldController::class, 'index'])
            ->name('world.index');
        Route::post('/world/unlock', [WorldController::class, 'unlock'])
            ->name('world.unlock');
        Route::post('/world/levels/{level}/complete', [WorldController::class, 'complete'])
            ->whereNumber('level')
            ->name('world.levels.complete');
        Route::post('/world/levels/{level}/session', [WorldLevelController::class, 'startSession'])
            ->whereNumber('level')
            ->name('world.levels.session');
        Route::post('/world/levels/{level}/answer', [WorldLevelController::class, 'answer'])
            ->whereNumber('level')
            ->name('world.levels.answer');
        Route::post('/world/levels/{level}/fail', [WorldLevelController::class, 'fail'])
            ->whereNumber('level')
            ->name('world.levels.fail');
        Route::post('/world/levels/{level}/skip-lockout', [WorldLevelController::class, 'skipLockout'])
            ->whereNumber('level')
            ->name('world.levels.skip-lockout');

        Route::post('/level-progress/{mode}/pass', [LevelProgressController::class, 'pass'])
            ->whereIn('mode', ['speaking', 'quiz'])
            ->name('level-progress.pass');
        Route::post('/level-progress/{mode}/question-pass', [LevelProgressController::class, 'questionPass'])
            ->whereIn('mode', ['speaking', 'quiz'])
            ->name('level-progress.question-pass');
        Route::post('/level-progress/{mode}/start-session', [LevelProgressController::class, 'startSession'])
            ->whereIn('mode', ['speaking', 'quiz'])
            ->name('level-progress.start-session');
        Route::post('/level-progress/{mode}/fail', [LevelProgressController::class, 'fail'])
            ->whereIn('mode', ['speaking', 'quiz'])
            ->name('level-progress.fail');
        Route::post('/level-progress/{mode}/reset', [LevelProgressController::class, 'reset'])
            ->whereIn('mode', ['speaking', 'quiz'])
            ->name('level-progress.reset');
        Route::post('/level-progress/{mode}/reset-tier', [LevelProgressController::class, 'resetTier'])
            ->whereIn('mode', ['speaking', 'quiz'])
            ->name('level-progress.reset-tier');
        Route::post('/level-progress/{mode}/attempt', [LevelProgressController::class, 'recordAttempt'])
            ->whereIn('mode', ['speaking', 'quiz'])
            ->name('level-progress.attempt');
        Route::post('/level-progress/{mode}/skip-lockout', [LevelProgressController::class, 'skipLockout'])
            ->whereIn('mode', ['speaking', 'quiz'])
            ->name('level-progress.skip-lockout');
        Route::post('/power-shop/purchases', [PowerShopController::class, 'store'])
            ->name('power-shop.purchases.store');
        Route::post('/power-shop/redeem', [PowerShopController::class, 'redeem'])
            ->middleware('throttle:10,1')
            ->name('power-shop.redeem');
        Route::get('/level-progress/{mode}/levels/{levelId}/review', [LevelProgressController::class, 'review'])
            ->whereIn('mode', ['speaking', 'quiz'])
            ->whereNumber('levelId')
            ->name('level-progress.review');
    });

    Route::middleware(['verified', 'role:administrator'])->prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])
            ->name('admin.dashboard');
        Route::get('/users', [AdminController::class, 'users'])
            ->name('admin.users');
        Route::get('/tracks', [AdminController::class, 'tracks'])
            ->name('admin.tracks');
        Route::patch('/tracks/{track}', [AdminController::class, 'updateTrack'])
            ->name('admin.tracks.update');
        Route::get('/reports', [AdminController::class, 'reports'])
            ->name('admin.reports');
    });
});
