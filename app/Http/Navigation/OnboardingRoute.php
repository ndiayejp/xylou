<?php

declare(strict_types=1);

namespace App\Http\Navigation;

use App\Domain\Children\Models\Onboarding;

// Adresse de chaque écran de l'onboarding (2 : profil de l'enfant ; 3 à 7 : étapes suivantes).
final class OnboardingRoute
{
    public static function for(Onboarding $onboarding, int $step): string
    {
        return $step <= Onboarding::FIRST_STEP
            ? route('onboarding.child', $onboarding)
            : route('onboarding.step', [$onboarding, $step]);
    }

    public static function resume(Onboarding $onboarding): string
    {
        return self::for($onboarding, $onboarding->reached_step);
    }
}
