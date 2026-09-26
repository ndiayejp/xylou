<?php

declare(strict_types=1);

use App\Domain\Children\Models\ChildProfile;
use App\Domain\Children\Models\LearningPreferences;
use App\Domain\Children\Models\Onboarding;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

// Parent dont l'enfant, l'univers et les objectifs sont décrits : l'écran 5 est ouvert.
function onboardingAtDifficulties(): Onboarding
{
    $parent = User::factory()->parent()->unverified()->create();
    test()->actingAs($parent)->post(route('onboarding.start'));
    $onboarding = Onboarding::query()->where('parent_id', $parent->id)->sole();
    test()->put(route('onboarding.child.update', $onboarding), [
        'first_name' => 'Emma', 'grade' => 'ce2', 'language' => 'fr',
    ]);
    test()->put(route('onboarding.interests.update', $onboarding), ['interests' => ['football'], 'custom' => []]);
    test()->put(route('onboarding.goals.update', $onboarding), [
        'goals' => ['regain_confidence'], 'primary' => 'regain_confidence',
    ]);

    return $onboarding->fresh() ?? $onboarding;
}

describe('écran 5 : difficultés', function (): void {
    test('affiche le catalogue par matière et le quotidien', function (): void {
        $onboarding = onboardingAtDifficulties();

        $this->get(route('onboarding.difficulties', $onboarding))
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Onboarding/Difficulties')
                ->where('childName', 'Emma')
                ->has('catalog', 5)
                ->where('catalog.maths.0', 'problem_solving')
                ->where('catalog.daily.0', 'discouraged_long')
                ->where('selected', [])
                ->where('observation', null));
    });

    test('les difficultés et l’observation sont enregistrées, l’écran 6 s’ouvre', function (): void {
        $onboarding = onboardingAtDifficulties();

        $this->put(route('onboarding.difficulties.update', $onboarding), [
            'difficulties' => ['maths.fractions', 'french.spelling', 'daily.tired_evening'],
            'observation' => ' Se perd quand l’énoncé est long. ',
        ])->assertRedirect(route('onboarding.preferences', $onboarding));

        $child = ChildProfile::query()->sole();
        expect($child->difficulties->map->id()->all())
            ->toBe(['maths.fractions', 'french.spelling', 'daily.tired_evening'])
            ->and($child->difficulty_observation)->toBe('Se perd quand l’énoncé est long.')
            ->and($onboarding->fresh()?->reached_step)->toBe(6);

        // L'observation est chiffrée en base.
        $stored = DB::table('child_profiles')->value('difficulty_observation');
        expect($stored)->toBeString()->not->toContain('énoncé');
    });

    test('un nouvel envoi remplace les choix sans doublon', function (): void {
        $onboarding = onboardingAtDifficulties();
        $this->put(route('onboarding.difficulties.update', $onboarding), [
            'difficulties' => ['maths.fractions', 'maths.geometry'], 'observation' => 'Note',
        ]);

        $this->put(route('onboarding.difficulties.update', $onboarding), [
            'difficulties' => ['maths.fractions'], 'observation' => '',
        ]);

        $child = ChildProfile::query()->sole();
        expect($child->difficulties->map->id()->all())->toBe(['maths.fractions'])
            ->and($child->difficulty_observation)->toBeNull();

        $this->get(route('onboarding.difficulties', $onboarding))
            ->assertInertia(fn (Assert $page): Assert => $page->where('selected', ['maths.fractions']));
    });

    test('« Je ne sais pas encore » passe à l’écran 6 sans rien enregistrer', function (): void {
        $onboarding = onboardingAtDifficulties();

        $this->post(route('onboarding.step.next', [$onboarding, 5]))
            ->assertRedirect(route('onboarding.preferences', $onboarding));

        expect(ChildProfile::query()->sole()->difficulties)->toBeEmpty();
    });

    test('refuse une difficulté hors catalogue ou une observation trop longue', function (array $data, string $field): void {
        $onboarding = onboardingAtDifficulties();

        $this->put(route('onboarding.difficulties.update', $onboarding), $data)->assertInvalid($field);
    })->with([
        'hors catalogue' => [['difficulties' => ['maths.dragons']], 'difficulties.0'],
        'observation trop longue' => [['observation' => str_repeat('a', 1001)], 'observation'],
    ]);
});

describe('écran 6 : préférences', function (): void {
    function onboardingAtPreferences(): Onboarding
    {
        $onboarding = onboardingAtDifficulties();
        test()->post(route('onboarding.step.next', [$onboarding, 5]));

        return $onboarding->fresh() ?? $onboarding;
    }

    test('affiche les façons d’apprendre et les durées, sans préférence par défaut', function (): void {
        $onboarding = onboardingAtPreferences();

        $this->get(route('onboarding.preferences', $onboarding))
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Onboarding/Preferences')
                ->where('styles', LearningPreferences::STYLES)
                ->where('durations', [5, 10, 15, 20])
                ->where('preferences', null));
    });

    test('les préférences sont enregistrées puis modifiables, l’écran 7 s’ouvre', function (): void {
        $onboarding = onboardingAtPreferences();

        $this->put(route('onboarding.preferences.update', $onboarding), [
            'styles' => ['visual', 'small_steps'], 'session_minutes' => 15, 'gentle_reminder' => true,
        ])->assertRedirect(route('onboarding.step', [$onboarding, 7]));

        $this->put(route('onboarding.preferences.update', $onboarding), [
            'styles' => ['interactive'], 'session_minutes' => 5, 'gentle_reminder' => false,
        ]);

        expect(LearningPreferences::count())->toBe(1);
        $this->get(route('onboarding.preferences', $onboarding))
            ->assertInertia(fn (Assert $page): Assert => $page->where('preferences', [
                'styles' => ['interactive'], 'sessionMinutes' => 5, 'gentleReminder' => false,
            ]));
    });

    test('refuse une façon d’apprendre ou une durée inconnue', function (array $data, string $field): void {
        $onboarding = onboardingAtPreferences();

        $this->put(route('onboarding.preferences.update', $onboarding), [
            'session_minutes' => 10, ...$data,
        ])->assertInvalid($field);
    })->with([
        'style inconnu' => [['styles' => ['hypnose']], 'styles.0'],
        'durée inconnue' => [['session_minutes' => 45], 'session_minutes'],
        'durée absente' => [['session_minutes' => null], 'session_minutes'],
    ]);

    test('on ne peut pas ouvrir l’écran 6 avant d’avoir passé le 5', function (): void {
        $onboarding = onboardingAtDifficulties();

        $this->get(route('onboarding.preferences', $onboarding))
            ->assertRedirect(route('onboarding.difficulties', $onboarding));
    });
});
