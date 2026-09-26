<?php

declare(strict_types=1);

use App\Domain\Identity\Enums\Role;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Parent\DashboardController as ParentDashboardController;
use App\Http\Controllers\Pro\DashboardController as ProDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome', [
    'canLogin' => Route::has('login'),
    'canRegister' => Route::has('register'),
    'laravelVersion' => Application::VERSION,
    'phpVersion' => PHP_VERSION,
]));

Route::get('/dashboard', HomeController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:'.Role::Parent->value])
    ->prefix('parent')
    ->name('parent.')
    ->group(function (): void {
        Route::get('/', ParentDashboardController::class)->name('dashboard');
    });

Route::middleware(['auth', 'verified', 'role:'.Role::Professional->value])
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
