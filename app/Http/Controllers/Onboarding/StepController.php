<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Domain\Children\Actions\AdvanceOnboarding;
use App\Domain\Children\Models\Onboarding;
use App\Http\Controllers\Controller;
use App\Http\Navigation\OnboardingRoute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

// Écrans pas encore construits (5 à 7). Provisoire : un écran d'attente, remplacé dans les PR suivantes.
final class StepController extends Controller
{
    public function show(Onboarding $onboarding, int $step): Response|RedirectResponse
    {
        Gate::authorize('update', $onboarding);

        if (! $onboarding->allows($step)) {
            return redirect(OnboardingRoute::resume($onboarding));
        }

        // Un écran qui a désormais sa propre page n'est plus servi par l'écran d'attente.
        if (OnboardingRoute::isDedicated($step)) {
            return redirect(OnboardingRoute::for($onboarding, $step));
        }

        return Inertia::render('Onboarding/Pending', [
            'onboarding' => ['id' => $onboarding->id, 'reachedStep' => $onboarding->reached_step],
            'step' => $step,
            'childName' => $onboarding->child?->first_name,
        ]);
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
            : redirect(OnboardingRoute::for($onboarding, $step + 1));
    }
}
