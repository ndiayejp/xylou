<?php

declare(strict_types=1);

use App\Domain\Identity\Enums\Role;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Kid\ExitController;
use App\Http\Controllers\Kid\HomeController as KidHomeController;
use App\Http\Controllers\Onboarding\ChildStepController;
use App\Http\Controllers\Onboarding\DifficultiesStepController;
use App\Http\Controllers\Onboarding\GoalsStepController;
use App\Http\Controllers\Onboarding\InterestsStepController;
use App\Http\Controllers\Onboarding\PreferencesStepController;
use App\Http\Controllers\Onboarding\StartController as OnboardingStartController;
use App\Http\Controllers\Onboarding\StepController as OnboardingStepController;
use App\Http\Controllers\Onboarding\SummaryStepController;
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

// Pas de « verified » ici : HomeController reprend d'abord un onboarding en cours.
Route::get('/dashboard', HomeController::class)->middleware('auth')->name('dashboard');

// Onboarding (écrans 2 à 7) : parent connecté, adresse pas encore forcément vérifiée.
Route::middleware(['auth', 'role:'.Role::Parent->value])
    ->prefix('onboarding')
    ->name('onboarding.')
    ->whereNumber('onboarding')
    ->group(function (): void {
        Route::post('/', OnboardingStartController::class)->name('start');
        Route::get('{onboarding}/enfant', [ChildStepController::class, 'show'])->name('child');
        Route::put('{onboarding}/enfant', [ChildStepController::class, 'update'])->name('child.update');
        Route::get('{onboarding}/univers', [InterestsStepController::class, 'show'])->name('interests');
        Route::put('{onboarding}/univers', [InterestsStepController::class, 'update'])->name('interests.update');
        Route::get('{onboarding}/objectifs', [GoalsStepController::class, 'show'])->name('goals');
        Route::put('{onboarding}/objectifs', [GoalsStepController::class, 'update'])->name('goals.update');
        Route::get('{onboarding}/difficultes', [DifficultiesStepController::class, 'show'])->name('difficulties');
        Route::put('{onboarding}/difficultes', [DifficultiesStepController::class, 'update'])->name('difficulties.update');
        Route::get('{onboarding}/preferences', [PreferencesStepController::class, 'show'])->name('preferences');
        Route::put('{onboarding}/preferences', [PreferencesStepController::class, 'update'])->name('preferences.update');
        Route::get('{onboarding}/resume', [SummaryStepController::class, 'show'])->name('summary');
        Route::put('{onboarding}/resume', [SummaryStepController::class, 'update'])->name('summary.update');
        Route::get('{onboarding}/etape/{step}', [OnboardingStepController::class, 'show'])
            ->whereNumber('step')->name('step');
        Route::post('{onboarding}/etape/{step}', [OnboardingStepController::class, 'store'])
            ->whereNumber('step')->name('step.next');
    });

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
