<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Domain\Children\Actions\AdvanceOnboarding;
use App\Domain\Children\Actions\SetLearningPreferences;
use App\Domain\Children\Models\LearningPreferences;
use App\Domain\Children\Models\Onboarding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

// Écran 6 « Préférences » : façons d'apprendre, durée d'une séance, rappel doux.
final class PreferencesStepController extends OnboardingStepController
{
    public function show(Onboarding $onboarding): Response|RedirectResponse
    {
        if (($redirect = $this->guard($onboarding)) instanceof RedirectResponse) {
            return $redirect;
        }
        $preferences = $this->child($onboarding)->learningPreferences;

        return Inertia::render('Onboarding/Preferences', [
            ...$this->props($onboarding),
            'styles' => LearningPreferences::STYLES,
            'durations' => LearningPreferences::SESSION_MINUTES,
            'preferences' => $preferences instanceof LearningPreferences ? [
                'styles' => $preferences->styles(),
                'sessionMinutes' => $preferences->session_minutes,
                'gentleReminder' => $preferences->gentle_reminder,
            ] : null,
        ]);
    }

    public function update(
        Request $request,
        Onboarding $onboarding,
        SetLearningPreferences $setPreferences,
        AdvanceOnboarding $advance,
    ): RedirectResponse {
        if (($redirect = $this->guard($onboarding)) instanceof RedirectResponse) {
            return $redirect;
        }

        $request->validate([
            'styles' => ['array'],
            'styles.*' => [Rule::in(LearningPreferences::STYLES)],
            'session_minutes' => ['required', 'integer', Rule::in(LearningPreferences::SESSION_MINUTES)],
            'gentle_reminder' => ['boolean'],
        ]);

        /** @var list<string> $styles */
        $styles = array_values($request->array('styles'));
        $setPreferences(
            $this->child($onboarding),
            $styles,
            $request->integer('session_minutes'),
            $request->boolean('gentle_reminder'),
        );

        return $this->next($onboarding, $advance);
    }

    protected function step(): int
    {
        return 6;
    }
}
