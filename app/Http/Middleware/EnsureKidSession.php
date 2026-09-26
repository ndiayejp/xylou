<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// Espace enfant (middleware « kid.session », §7.3) : seulement avec une session enfant ouverte.
final class EnsureKidSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('kid')->check()) {
            return to_route('login');
        }

        return $next($request);
    }
}
