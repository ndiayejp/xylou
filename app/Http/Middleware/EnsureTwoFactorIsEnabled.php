<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Identity\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Un compte qui doit avoir la 2FA (pro) est renvoyé vers la page Sécurité tant qu'elle n'est pas confirmée.
final class EnsureTwoFactorIsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && $user->requiresTwoFactor() && ! $user->hasEnabledTwoFactorAuthentication()) {
            return to_route('settings.security');
        }

        return $next($request);
    }
}
