<?php

declare(strict_types=1);

namespace App\Http\Navigation;

use App\Domain\Children\Models\Onboarding;

// Adresse de chaque écran de l'onboarding (2 à 7).
final class OnboardingRoute
{
    /** @var array<int, string> */
    private const array ROUTES = [
        2 => 'onboarding.child',
        3 => 'onboarding.interests',
        4 => 'onboarding.goals',
        5 => 'onboarding.difficulties',
        6 => 'onboarding.preferences',
        7 => 'onboarding.summary',
    ];

    public static function for(Onboarding $onboarding, int $step): string
    {
        $step = min(max($step, Onboarding::FIRST_STEP), Onboarding::LAST_STEP);

        return route(self::ROUTES[$step], $onboarding);
    }

    public static function resume(Onboarding $onboarding): string
    {
        return self::for($onboarding, $onboarding->reached_step);
    }

    // Après l'enregistrement d'un écran : le suivant, ou le résumé si le parent l'a déjà atteint
    // (il y revient après avoir corrigé une section).
    public static function after(Onboarding $onboarding, int $step): string
    {
        return self::for($onboarding, $onboarding->reached_step >= Onboarding::LAST_STEP ? Onboarding::LAST_STEP : $step + 1);
    }
}
