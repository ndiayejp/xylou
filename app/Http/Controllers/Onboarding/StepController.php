<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Domain\Children\Actions\AdvanceOnboarding;
use App\Domain\Children\Models\Onboarding;
use App\Http\Controllers\Controller;
use App\Http\Navigation\OnboardingRoute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

// Adresse générique d'un écran : renvoie vers sa page, ou passe l'écran sans rien enregistrer
// (« Je ne sais pas encore » à l'écran 5).
final class StepController extends Controller
{
    public function show(Onboarding $onboarding, int $step): RedirectResponse
    {
        Gate::authorize('update', $onboarding);

        return redirect($onboarding->allows($step)
            ? OnboardingRoute::for($onboarding, $step)
            : OnboardingRoute::resume($onboarding));
    }

    public function store(Onboarding $onboarding, int $step, AdvanceOnboarding $advance): RedirectResponse
    {
        Gate::authorize('update', $onboarding);

        if (! $onboarding->allows($step)) {
            return redirect(OnboardingRoute::resume($onboarding));
        }

        $advance($onboarding, $step);

        return $onboarding->isCompleted()
            ? to_route('dashboard')
            : redirect(OnboardingRoute::after($onboarding, $step));
    }
}
