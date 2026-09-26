<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Inertia\Response;

// Page protégée par password.confirm : elle peut montrer le QR code, la clé et les codes de secours.
final class SecurityController extends Controller
{
    public function __invoke(#[CurrentUser] User $user): Response
    {
        $enabled = $user->hasEnabledTwoFactorAuthentication();
        $pending = ! $enabled && $user->two_factor_secret !== null;

        return Inertia::render('Settings/Security', [
            'twoFactor' => [
                'enabled' => $enabled,
                'pending' => $pending,
                'required' => $user->requiresTwoFactor(),
                'qrCodeSvg' => $pending ? $user->twoFactorQrCodeSvg() : null,
                'setupKey' => $pending ? Crypt::decrypt($user->two_factor_secret) : null,
                'recoveryCodes' => $enabled ? $user->recoveryCodes() : [],
            ],
            'status' => session('status'),
        ]);
    }
}
