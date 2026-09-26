<?php

declare(strict_types=1);

namespace App\Domain\Children\Actions;

use App\Domain\Children\Models\Onboarding;
use App\Domain\Identity\Models\User;

// Démarre la description d'un enfant (premier ou suivant), ou reprend celle en cours.
final class StartOnboarding
{
    public function __invoke(User $parent): Onboarding
    {
        $current = Onboarding::query()->inProgressFor($parent)->first();

        if ($current instanceof Onboarding && $current->child_profile_id === null) {
            return $current;
        }

        $onboarding = new Onboarding(['reached_step' => Onboarding::FIRST_STEP]);
        $onboarding->parent()->associate($parent);
        $onboarding->save();

        return $onboarding;
    }
}
