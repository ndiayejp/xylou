<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Domain\Children\Actions\StartOnboarding;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Navigation\OnboardingRoute;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;

// « Ajouter un enfant » : mêmes écrans 2 à 7 que le premier (choix du 2026-09-26).
final class StartController extends Controller
{
    public function __invoke(#[CurrentUser] User $parent, StartOnboarding $startOnboarding): RedirectResponse
    {
        return redirect(OnboardingRoute::resume($startOnboarding($parent)));
    }
}
