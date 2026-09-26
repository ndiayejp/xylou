<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Domain\Children\Actions\AdvanceOnboarding;
use App\Domain\Children\Actions\UpdateInterests;
use App\Domain\Children\Models\CustomInterest;
use App\Domain\Children\Models\Interest;
use App\Domain\Children\Models\Onboarding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

// Écran 3 « Son univers » : au moins une passion (référentiel ou libre), idéalement 3 à 5.
final class InterestsStepController extends OnboardingStepController
{
    public const int MAX_CUSTOM = 5;

    public function show(Onboarding $onboarding): Response|RedirectResponse
    {
        if (($redirect = $this->guard($onboarding)) instanceof RedirectResponse) {
            return $redirect;
        }
        $child = $this->child($onboarding);

        return Inertia::render('Onboarding/Interests', [
            ...$this->props($onboarding),
            'catalog' => Interest::query()->orderBy('position')->get(['key', 'category'])
                ->map(fn (Interest $interest): array => ['key' => $interest->key, 'category' => $interest->category]),
            'selected' => $child->interests->pluck('key'),
            'custom' => $child->customInterests->map(fn (CustomInterest $interest): string => $interest->label),
        ]);
    }

    public function update(
        Request $request,
        Onboarding $onboarding,
        UpdateInterests $updateInterests,
        AdvanceOnboarding $advance,
    ): RedirectResponse {
        if (($redirect = $this->guard($onboarding)) instanceof RedirectResponse) {
            return $redirect;
        }

        $request->validate([
            'interests' => ['array'],
            'interests.*' => ['string', Rule::exists('interests', 'key')],
            'custom' => ['array', 'max:'.self::MAX_CUSTOM],
            'custom.*' => ['nullable', 'string', 'max:40'],
        ]);

        /** @var list<string> $keys */
        $keys = array_values($request->array('interests'));
        /** @var list<string> $custom */
        $custom = array_values(array_filter(array_map(
            fn (mixed $label): string => is_string($label) ? trim($label) : '',
            $request->array('custom'),
        )));

        if ($keys === [] && $custom === []) {
            return back()->withErrors(['interests' => __('onboarding.interests_required')]);
        }

        $updateInterests($this->child($onboarding), $keys, $custom);

        return $this->next($onboarding, $advance);
    }

    protected function step(): int
    {
        return 3;
    }
}
