<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Domain\Children\Actions\AdvanceOnboarding;
use App\Domain\Children\Actions\RecordDifficulties;
use App\Domain\Children\Models\ChildDifficulty;
use App\Domain\Children\Models\Onboarding;
use App\Domain\Children\Support\DifficultyCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

// Écran 5 « Difficultés » : facultatif, privé ; « Je ne sais pas encore » passe par onboarding.step.next.
final class DifficultiesStepController extends OnboardingStepController
{
    public function show(Onboarding $onboarding): Response|RedirectResponse
    {
        if (($redirect = $this->guard($onboarding)) instanceof RedirectResponse) {
            return $redirect;
        }
        $child = $this->child($onboarding);

        return Inertia::render('Onboarding/Difficulties', [
            ...$this->props($onboarding),
            'catalog' => DifficultyCatalog::ITEMS,
            'selected' => $child->difficulties->map(fn (ChildDifficulty $difficulty): string => $difficulty->id()),
            'observation' => $child->difficulty_observation,
        ]);
    }

    public function update(
        Request $request,
        Onboarding $onboarding,
        RecordDifficulties $recordDifficulties,
        AdvanceOnboarding $advance,
    ): RedirectResponse {
        if (($redirect = $this->guard($onboarding)) instanceof RedirectResponse) {
            return $redirect;
        }

        $request->validate([
            'difficulties' => ['array'],
            'difficulties.*' => [Rule::in(DifficultyCatalog::ids())],
            'observation' => ['nullable', 'string', 'max:1000'],
        ]);

        /** @var list<string> $ids */
        $ids = array_values($request->array('difficulties'));
        $recordDifficulties(
            $this->child($onboarding),
            $ids,
            $request->filled('observation') ? $request->string('observation')->toString() : null,
        );

        return $this->next($onboarding, $advance);
    }

    protected function step(): int
    {
        return 5;
    }
}
