<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureKidSession;
use App\Http\Middleware\EnsureTwoFactorIsEnabled;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\KeepKidInKidSpace;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            KeepKidInKidSpace::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Le verrou de la session enfant doit passer avant « auth » (qui, sinon, renverrait vers la connexion).
        // (la liste de priorité de Laravel référence l'interface AuthenticatesRequests, pas la classe).
        $middleware->prependToPriorityList(AuthenticatesRequests::class, KeepKidInKidSpace::class);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'two-factor.required' => EnsureTwoFactorIsEnabled::class,
            'kid.session' => EnsureKidSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
