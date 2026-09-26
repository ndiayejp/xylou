<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// Invariant : pendant une session enfant, aucune route hors de l'espace enfant n'est atteignable
// (espace parent, pro, profil, connexion…). Appliqué à tout le groupe web.
final class KeepKidInKidSpace
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('kid')->check() && ! $request->routeIs('kid.*')) {
            return to_route('kid.home');
        }

        return $next($request);
    }
}
