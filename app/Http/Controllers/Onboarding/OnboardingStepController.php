<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Domain\Children\Actions\AdvanceOnboarding;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Children\Models\Onboarding;
use App\Http\Controllers\Controller;
use App\Http\Navigation\OnboardingRoute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

// Base des écrans 3 à 6 : autorisation, étape accessible, enfant décrit, passage à l'écran suivant.
abstract class OnboardingStepController extends Controller
{
    abstract protected function step(): int;

    // Redirige vers l'étape où reprendre si celle-ci n'est pas encore accessible.
    protected function guard(Onboarding $onboarding): ?RedirectResponse
    {
        Gate::authorize('update', $onboarding);

        return $onboarding->allows($this->step()) ? null : redirect(OnboardingRoute::resume($onboarding));
    }

    protected function child(Onboarding $onboarding): ChildProfile
    {
        $child = $onboarding->child;
        abort_unless($child instanceof ChildProfile, 404);

        return $child;
    }

    /** @return array<string, mixed> */
    protected function props(Onboarding $onboarding): array
    {
        return [
            'onboarding' => ['id' => $onboarding->id, 'reachedStep' => $onboarding->reached_step],
            'childName' => $onboarding->child?->first_name,
        ];
    }

    protected function next(Onboarding $onboarding, AdvanceOnboarding $advance): RedirectResponse
    {
        $advance($onboarding, $this->step());

        return redirect(OnboardingRoute::for($onboarding, $this->step() + 1));
    }
}
