<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Identity\Listeners\AuditSubscriber;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Event::subscribe(AuditSubscriber::class);

        // 12 caractères minimum (maquette « Votre compte ») ; en production, refus des mots de passe
        // connus dans des fuites (Have I Been Pwned ne reçoit qu'un préfixe du hash).
        Password::defaults(function (): Password {
            $rule = Password::min(12);

            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });
    }
}
