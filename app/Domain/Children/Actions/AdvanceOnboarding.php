<?php

declare(strict_types=1);

namespace App\Domain\Children\Actions;

use App\Domain\Children\Models\Onboarding;

// Une étape vient d'être validée : l'étape suivante devient accessible ; la dernière termine l'onboarding.
final class AdvanceOnboarding
{
    public function __invoke(Onboarding $onboarding, int $completedStep): Onboarding
    {
        $onboarding->reached_step = max($onboarding->reached_step, min($completedStep + 1, Onboarding::LAST_STEP));

        if ($completedStep >= Onboarding::LAST_STEP) {
            $onboarding->completed_at ??= now();
        }

        $onboarding->save();

        return $onboarding;
    }
}
