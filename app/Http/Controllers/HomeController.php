<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Children\Models\Onboarding;
use App\Domain\Identity\Models\User;
use App\Http\Navigation\OnboardingRoute;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;

// Route « dashboard » (après connexion) : reprend l'onboarding en cours, sinon exige l'adresse vérifiée
// (l'onboarding passe avant la vérification, choix du 2026-09-26), puis aiguille vers l'espace du compte.
final class HomeController extends Controller
{
    public function __invoke(#[CurrentUser] User $user): RedirectResponse
    {
        $onboarding = Onboarding::query()->inProgressFor($user)->first();

        if ($onboarding instanceof Onboarding) {
            return redirect(OnboardingRoute::resume($onboarding));
        }

        if (! $user->hasVerifiedEmail()) {
            return to_route('verification.notice');
        }

        $route = $user->homeRouteName();
        abort_if($route === null, 403);

        return to_route($route);
    }
}
