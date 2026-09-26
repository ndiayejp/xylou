<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Domain\Children\Actions\AdvanceOnboarding;
use App\Domain\Children\Actions\StartOnboarding;
use App\Domain\Children\Models\ChildDifficulty;
use App\Domain\Children\Models\ChildGoal;
use App\Domain\Children\Models\CustomInterest;
use App\Domain\Children\Models\Interest;
use App\Domain\Children\Models\LearningPreferences;
use App\Domain\Children\Models\Onboarding;
use App\Http\Navigation\OnboardingRoute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// Écran 7 « Résumé » : chaque section renvoie vers son écran, qui ramène ici une fois enregistré.
final class SummaryStepController extends OnboardingStepController
{
    public function show(Onboarding $onboarding): Response|RedirectResponse
    {
        if (($redirect = $this->guard($onboarding)) instanceof RedirectResponse) {
            return $redirect;
        }
        $child = $this->child($onboarding);
        $preferences = $child->learningPreferences;

        return Inertia::render('Onboarding/Summary', [
            ...$this->props($onboarding),
            'summary' => [
                'avatar' => $child->avatar_key?->value,
                'grade' => $child->grade->value,
                'age' => $child->birth_year === null ? null : now()->year - $child->birth_year,
                'interests' => $child->interests->map(fn (Interest $interest): string => $interest->key),
                'customInterests' => $child->customInterests->map(fn (CustomInterest $custom): string => $custom->label),
                'goals' => $child->goals->map(fn (ChildGoal $goal): array => [
                    'goal' => $goal->goal->value,
                    'primary' => $goal->is_primary,
                    'note' => $goal->note,
                ]),
                'difficulties' => $child->difficulties->map(fn (ChildDifficulty $difficulty): string => $difficulty->id()),
                'styles' => $preferences?->styles() ?? [],
                'sessionMinutes' => $preferences instanceof LearningPreferences ? $preferences->session_minutes : null,
                'gentleReminder' => $preferences instanceof LearningPreferences && $preferences->gentle_reminder,
                'comfort' => array_values(array_filter(
                    ['read_aloud', 'dyslexia_font', 'no_timer'],
                    $child->comfort(...),
                )),
            ],
        ]);
    }

    // Termine l'onboarding ; « Ajouter un autre enfant » ouvre aussitôt le suivant.
    public function update(
        Request $request,
        Onboarding $onboarding,
        AdvanceOnboarding $advance,
        StartOnboarding $startOnboarding,
    ): RedirectResponse {
        if (($redirect = $this->guard($onboarding)) instanceof RedirectResponse) {
            return $redirect;
        }

        $request->validate(['add_child' => ['boolean']]);

        $advance($onboarding, $this->step());

        return $request->boolean('add_child')
            ? redirect(OnboardingRoute::resume($startOnboarding($onboarding->parent)))
            : to_route('dashboard');
    }

    protected function step(): int
    {
        return 7;
    }
}
