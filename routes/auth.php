<?php

declare(strict_types=1);

use App\Domain\Identity\Enums\Role;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\RecoveryCodeController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;

Route::middleware('guest')->group(function (): void {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');

    // 2FA à la connexion (Fortify) : identifiants déjà validés, code attendu.
    Route::get('two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'create'])
        ->name('two-factor.login');

    Route::post('two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:two-factor')
        ->name('two-factor.login.store');
});

// Gestion de la 2FA (Fortify, ADR 0012), toujours derrière une confirmation du mot de passe.
Route::middleware(['auth', 'password.confirm'])->group(function (): void {
    Route::get('settings/security', SecurityController::class)->name('settings.security');

    Route::post('user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])
        ->name('two-factor.enable');

    Route::post('user/confirmed-two-factor-authentication', [ConfirmedTwoFactorAuthenticationController::class, 'store'])
        ->name('two-factor.confirm');

    // Obligatoire pour les pros : seul un parent peut la désactiver.
    Route::delete('user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])
        ->middleware('role:'.Role::Parent->value)
        ->name('two-factor.disable');

    Route::post('user/two-factor-recovery-codes', [RecoveryCodeController::class, 'store'])
        ->name('two-factor.regenerate-recovery-codes');
});

Route::middleware('auth')->group(function (): void {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
