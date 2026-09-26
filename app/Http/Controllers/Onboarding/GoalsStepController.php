<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Domain\Children\Actions\AdvanceOnboarding;
use App\Domain\Children\Actions\SetGoals;
use App\Domain\Children\Enums\Goal;
use App\Domain\Children\Models\ChildGoal;
use App\Domain\Children\Models\Onboarding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

// Écran 4 « Objectifs » : plusieurs choix, un principal parmi eux ; note libre pour « Autre objectif ».
final class GoalsStepController extends OnboardingStepController
{
    public function show(Onboarding $onboarding): Response|RedirectResponse
    {
        if (($redirect = $this->guard($onboarding)) instanceof RedirectResponse) {
            return $redirect;
        }
        $goals = $this->child($onboarding)->goals;
        $primary = $goals->first(fn (ChildGoal $goal): bool => $goal->is_primary);

        return Inertia::render('Onboarding/Goals', [
            ...$this->props($onboarding),
            'goals' => array_column(Goal::cases(), 'value'),
            'selected' => $goals->map(fn (ChildGoal $goal): string => $goal->goal->value),
            'primary' => $primary?->goal->value,
            'note' => $goals->first(fn (ChildGoal $goal): bool => $goal->goal === Goal::Other)?->note,
        ]);
    }

    public function update(
        Request $request,
        Onboarding $onboarding,
        SetGoals $setGoals,
        AdvanceOnboarding $advance,
    ): RedirectResponse {
        if (($redirect = $this->guard($onboarding)) instanceof RedirectResponse) {
            return $redirect;
        }

        $request->validate([
            'goals' => ['required', 'array', 'min:1'],
            'goals.*' => [Rule::enum(Goal::class)],
            'primary' => ['required', Rule::enum(Goal::class), Rule::in($request->array('goals'))],
            'note' => ['nullable', 'string', 'max:200', Rule::requiredIf(in_array(Goal::Other->value, $request->array('goals'), true))],
        ]);

        $goals = array_values(array_map(
            fn (mixed $goal): Goal => Goal::from((string) $goal),
            $request->array('goals'),
        ));

        $setGoals(
            $this->child($onboarding),
            $goals,
            $request->enum('primary', Goal::class) ?? $goals[0],
            $request->filled('note') ? $request->string('note')->trim()->toString() : null,
        );

        return $this->next($onboarding, $advance);
    }

    protected function step(): int
    {
        return 4;
    }
}
