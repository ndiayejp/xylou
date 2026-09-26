<?php

declare(strict_types=1);

namespace App\Http\Navigation;

use App\Domain\Children\Models\Onboarding;

// Adresse de chaque écran de l'onboarding (2 à 7). Les écrans pas encore construits passent par
// la route générique « onboarding.step » (écran d'attente).
final class OnboardingRoute
{
    /** @var array<int, string> */
    private const array ROUTES = [
        2 => 'onboarding.child',
        3 => 'onboarding.interests',
        4 => 'onboarding.goals',
    ];

    public static function for(Onboarding $onboarding, int $step): string
    {
        $step = max($step, Onboarding::FIRST_STEP);

        return isset(self::ROUTES[$step])
            ? route(self::ROUTES[$step], $onboarding)
            : route('onboarding.step', [$onboarding, $step]);
    }

    public static function resume(Onboarding $onboarding): string
    {
        return self::for($onboarding, $onboarding->reached_step);
    }

    // L'écran a-t-il sa propre route ? (sinon, écran d'attente générique)
    public static function isDedicated(int $step): bool
    {
        return isset(self::ROUTES[$step]);
    }
}
