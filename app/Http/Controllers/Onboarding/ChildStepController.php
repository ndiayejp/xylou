<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Domain\Children\Actions\SaveOnboardingChild;
use App\Domain\Children\Data\ChildProfileInput;
use App\Domain\Children\Enums\AvatarKey;
use App\Domain\Children\Enums\Grade;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Children\Models\Onboarding;
use App\Http\Controllers\Controller;
use App\Http\Navigation\OnboardingRoute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

// Écran 2 « Profil de l'enfant ». On demande l'âge, on ne garde que l'année de naissance (§11.1).
final class ChildStepController extends Controller
{
    public const int MIN_AGE = 5;

    public const int MAX_AGE = 16;

    public function show(Onboarding $onboarding): Response
    {
        Gate::authorize('update', $onboarding);
        $child = $onboarding->child;

        return Inertia::render('Onboarding/ChildProfile', [
            'onboarding' => ['id' => $onboarding->id, 'reachedStep' => $onboarding->reached_step],
            'child' => $child instanceof ChildProfile ? [
                'firstName' => $child->first_name,
                'age' => $child->birth_year === null ? null : now()->year - $child->birth_year,
                'grade' => $child->grade->value,
                'language' => $child->language,
                'avatar' => $child->avatar_key?->value,
                'readAloud' => $child->comfort('read_aloud'),
                'dyslexiaFont' => $child->comfort('dyslexia_font'),
                'noTimer' => $child->comfort('no_timer'),
            ] : null,
        ]);
    }

    public function update(Request $request, Onboarding $onboarding, SaveOnboardingChild $saveChild): RedirectResponse
    {
        Gate::authorize('update', $onboarding);

        $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'age' => ['nullable', 'integer', 'between:'.self::MIN_AGE.','.self::MAX_AGE],
            'grade' => ['required', Rule::enum(Grade::class)],
            'language' => ['required', Rule::in(['fr'])],
            'avatar' => ['nullable', Rule::enum(AvatarKey::class)],
            'read_aloud' => ['boolean'],
            'dyslexia_font' => ['boolean'],
            'no_timer' => ['boolean'],
        ]);

        $age = $request->filled('age') ? $request->integer('age') : null;

        $saveChild($onboarding, new ChildProfileInput(
            firstName: $request->string('first_name')->trim()->toString(),
            grade: $request->enum('grade', Grade::class) ?? Grade::Cp,
            birthYear: $age === null ? null : now()->year - $age,
            language: $request->string('language')->toString(),
            avatar: $request->enum('avatar', AvatarKey::class),
            readAloud: $request->boolean('read_aloud'),
            dyslexiaFont: $request->boolean('dyslexia_font'),
            noTimer: $request->boolean('no_timer'),
        ));

        return redirect(OnboardingRoute::after($onboarding, Onboarding::FIRST_STEP));
    }
}
