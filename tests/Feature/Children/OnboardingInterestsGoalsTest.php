<?php

declare(strict_types=1);

use App\Domain\Children\Enums\Goal;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Children\Models\Interest;
use App\Domain\Children\Models\Onboarding;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

// Parent dont l'enfant est décrit (écran 2 validé) : l'écran 3 est ouvert.
function onboardingAtInterests(): Onboarding
{
    $parent = User::factory()->parent()->unverified()->create();
    test()->actingAs($parent)->post(route('onboarding.start'));
    $onboarding = Onboarding::query()->where('parent_id', $parent->id)->sole();
    test()->put(route('onboarding.child.update', $onboarding), [
        'first_name' => 'Emma', 'grade' => 'ce2', 'language' => 'fr',
    ]);

    return $onboarding->fresh() ?? $onboarding;
}

test('le référentiel des passions est en base, dans l’ordre de la maquette', function (): void {
    expect(Interest::query()->orderBy('position')->pluck('key')->take(3)->all())
        ->toBe(['football', 'space', 'lego'])
        ->and(Interest::count())->toBe(15);
});

describe('écran 3 : son univers', function (): void {
    test('affiche le référentiel et les choix déjà faits', function (): void {
        $onboarding = onboardingAtInterests();

        $this->get(route('onboarding.interests', $onboarding))
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Onboarding/Interests')
                ->where('childName', 'Emma')
                ->has('catalog', 15)
                ->where('catalog.0', ['key' => 'football', 'category' => 'sports'])
                ->where('selected', [])
                ->where('custom', []));
    });

    test('passions du référentiel dans l’ordre choisi, et passions libres', function (): void {
        $onboarding = onboardingAtInterests();

        $this->put(route('onboarding.interests.update', $onboarding), [
            'interests' => ['space', 'football', 'lego'],
            'custom' => ['Les robots', ' Son club de foot ', ''],
        ])->assertRedirect(route('onboarding.goals', $onboarding));

        $child = ChildProfile::query()->sole();
        expect($child->interests->pluck('key')->all())->toBe(['space', 'football', 'lego'])
            ->and($child->customInterests->pluck('label')->all())->toBe(['Les robots', 'Son club de foot'])
            ->and($onboarding->fresh()?->reached_step)->toBe(4);

        $this->get(route('onboarding.interests', $onboarding))
            ->assertInertia(fn (Assert $page): Assert => $page
                ->where('selected', ['space', 'football', 'lego'])
                ->where('custom', ['Les robots', 'Son club de foot']));
    });

    test('revenir et modifier remplace les choix, sans doublon', function (): void {
        $onboarding = onboardingAtInterests();
        $this->put(route('onboarding.interests.update', $onboarding), ['interests' => ['space', 'lego'], 'custom' => ['Les robots']]);

        $this->put(route('onboarding.interests.update', $onboarding), ['interests' => ['ocean'], 'custom' => []]);

        $child = ChildProfile::query()->sole();
        expect($child->interests->pluck('key')->all())->toBe(['ocean'])
            ->and($child->customInterests)->toBeEmpty();
    });

    test('une passion libre seule suffit', function (): void {
        $onboarding = onboardingAtInterests();

        $this->put(route('onboarding.interests.update', $onboarding), ['interests' => [], 'custom' => ['Les pirates']])
            ->assertRedirect(route('onboarding.goals', $onboarding));
    });

    test('au moins une passion est demandée', function (): void {
        $onboarding = onboardingAtInterests();

        $this->put(route('onboarding.interests.update', $onboarding), ['interests' => [], 'custom' => ['  ']])
            ->assertSessionHasErrors(['interests' => 'Choisissez au moins une passion, dans la liste ou en l’écrivant.']);

        expect($onboarding->fresh()?->reached_step)->toBe(3);
    });

    test('passion inconnue ou trop de passions libres refusées', function (array $data, string $field): void {
        $onboarding = onboardingAtInterests();

        $this->put(route('onboarding.interests.update', $onboarding), $data)->assertSessionHasErrors($field);
    })->with([
        'inconnue' => [['interests' => ['licornes'], 'custom' => []], 'interests.0'],
        'six libres' => [['interests' => [], 'custom' => ['a', 'b', 'c', 'd', 'e', 'f']], 'custom'],
        'trop longue' => [['interests' => [], 'custom' => [str_repeat('x', 41)]], 'custom.0'],
    ]);

    test('l’écran 3 exige l’écran 2', function (): void {
        $parent = User::factory()->parent()->create();
        $this->actingAs($parent)->post(route('onboarding.start'));
        $onboarding = Onboarding::query()->sole();

        $this->get(route('onboarding.interests', $onboarding))->assertRedirect(route('onboarding.child', $onboarding));
    });
});

describe('écran 4 : objectifs', function (): void {
    function onboardingAtGoals(): Onboarding
    {
        $onboarding = onboardingAtInterests();
        test()->put(route('onboarding.interests.update', $onboarding), ['interests' => ['football'], 'custom' => []]);

        return $onboarding;
    }

    test('plusieurs objectifs, un principal, note pour « Autre »', function (): void {
        $onboarding = onboardingAtGoals();

        $this->put(route('onboarding.goals.update', $onboarding), [
            'goals' => ['regain_confidence', 'progress_maths', 'other'],
            'primary' => 'progress_maths',
            'note' => 'Préparer l’entrée en 6e',
        ])->assertRedirect(route('onboarding.difficulties', $onboarding));

        $goals = ChildProfile::query()->sole()->goals;
        expect($goals->first()?->goal)->toBe(Goal::ProgressMaths)
            ->and($goals->first()?->is_primary)->toBeTrue()
            ->and($goals->where('is_primary', true))->toHaveCount(1)
            ->and($goals->firstWhere('goal', Goal::Other)?->note)->toBe('Préparer l’entrée en 6e')
            ->and($onboarding->fresh()?->reached_step)->toBe(5);

        $this->get(route('onboarding.goals', $onboarding))
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Onboarding/Goals')
                ->where('primary', 'progress_maths')
                ->where('note', 'Préparer l’entrée en 6e')
                ->has('selected', 3));
    });

    test('règles de validation', function (array $data, string $field): void {
        $onboarding = onboardingAtGoals();

        $this->put(route('onboarding.goals.update', $onboarding), $data)->assertSessionHasErrors($field);
    })->with([
        'aucun objectif' => [['goals' => [], 'primary' => 'autonomy'], 'goals'],
        'principal hors sélection' => [['goals' => ['autonomy'], 'primary' => 'spelling'], 'primary'],
        'objectif inconnu' => [['goals' => ['devenir_astronaute'], 'primary' => 'devenir_astronaute'], 'goals.0'],
        '« Autre » sans précision' => [['goals' => ['other'], 'primary' => 'other', 'note' => ''], 'note'],
    ]);

    test('modifier remplace les objectifs', function (): void {
        $onboarding = onboardingAtGoals();
        $this->put(route('onboarding.goals.update', $onboarding), ['goals' => ['autonomy', 'spelling'], 'primary' => 'autonomy']);

        $this->put(route('onboarding.goals.update', $onboarding), ['goals' => ['comprehension'], 'primary' => 'comprehension']);

        expect(ChildProfile::query()->sole()->goals->pluck('goal')->all())->toBe([Goal::Comprehension]);
    });

    test('l’ancienne adresse générique de l’écran 4 renvoie vers la vraie page', function (): void {
        $onboarding = onboardingAtGoals();

        $this->get(route('onboarding.step', [$onboarding, 4]))->assertRedirect(route('onboarding.goals', $onboarding));
    });
});
