<?php

declare(strict_types=1);

use App\Domain\Identity\Enums\Role;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Kid\ExitController;
use App\Http\Controllers\Kid\HomeController as KidHomeController;
use App\Http\Controllers\Parent\CurrentChildController;
use App\Http\Controllers\Parent\DashboardController as ParentDashboardController;
use App\Http\Controllers\Parent\KidSessionController;
use App\Http\Controllers\Pro\DashboardController as ProDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\HomeController as PublicHomeController;
use App\Http\Controllers\Public\LegalController;
use App\Http\Controllers\Public\SitemapController;
use Illuminate\Support\Facades\Route;

// Pages publiques
Route::get('/', PublicHomeController::class)->name('home');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

foreach ([
    'notice' => '/mentions-legales',
    'privacy' => '/confidentialite',
    'terms' => '/conditions-utilisation',
    'accessibility' => '/accessibilite',
] as $page => $uri) {
    Route::get($uri, LegalController::class)->defaults('page', $page)->name('legal.'.$page);
}

Route::get('/dashboard', HomeController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:'.Role::Parent->value])
    ->prefix('parent')
    ->name('parent.')
    ->group(function (): void {
        Route::get('/', ParentDashboardController::class)->name('dashboard');
        Route::post('current-child', CurrentChildController::class)->name('current-child');
        Route::post('children/{child}/kid-session', KidSessionController::class)->name('children.kid-session');
    });

// Espace enfant (garde « kid ») ; le middleware KeepKidInKidSpace y retient l'enfant.
Route::middleware('kid.session')
    ->prefix('enfant')
    ->name('kid.')
    ->group(function (): void {
        Route::get('/', KidHomeController::class)->name('home');
        Route::get('sortie', [ExitController::class, 'show'])->name('exit');
        Route::post('sortie', [ExitController::class, 'store'])->name('exit.store');
    });

Route::middleware(['auth', 'verified', 'role:'.Role::Professional->value, 'two-factor.required'])
    ->prefix('pro')
    ->name('pro.')
    ->group(function (): void {
        Route::get('/', ProDashboardController::class)->name('dashboard');
    });

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
