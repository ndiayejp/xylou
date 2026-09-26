<?php

declare(strict_types=1);

use App\Domain\Children\Models\ChildProfile;
use App\Domain\Children\Models\Onboarding;
use Inertia\Testing\AssertableInertia as Assert;

// Parent arrivé au résumé, avec des réponses dans chaque section.
function onboardingAtSummary(): Onboarding
{
    [, $onboarding] = freshParent();
    test()->put(route('onboarding.child.update', $onboarding), childData());
    test()->put(route('onboarding.interests.update', $onboarding), [
        'interests' => ['space', 'football'], 'custom' => ['Les robots'],
    ]);
    test()->put(route('onboarding.goals.update', $onboarding), [
        'goals' => ['regain_confidence', 'other'], 'primary' => 'regain_confidence', 'note' => 'Entrée en 6e',
    ]);
    test()->put(route('onboarding.difficulties.update', $onboarding), [
        'difficulties' => ['maths.fractions', 'daily.concentration'], 'observation' => 'Note privée',
    ]);
    test()->put(route('onboarding.preferences.update', $onboarding), [
        'styles' => ['visual'], 'session_minutes' => 15, 'gentle_reminder' => true,
    ]);

    return $onboarding->fresh() ?? $onboarding;
}

test('le résumé reprend chaque section, sans l’observation privée', function (): void {
    $onboarding = onboardingAtSummary();

    $this->get(route('onboarding.summary', $onboarding))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Onboarding/Summary')
            ->where('childName', 'Lucas')
            ->where('summary', [
                'avatar' => 'ball',
                'grade' => '6e',
                'age' => 11,
                'interests' => ['space', 'football'],
                'customInterests' => ['Les robots'],
                'goals' => [
                    ['goal' => 'regain_confidence', 'primary' => true, 'note' => null],
                    ['goal' => 'other', 'primary' => false, 'note' => 'Entrée en 6e'],
                ],
                'difficulties' => ['maths.fractions', 'daily.concentration'],
                'styles' => ['visual'],
                'sessionMinutes' => 15,
                'gentleReminder' => true,
                'comfort' => ['read_aloud', 'no_timer'],
            ]));
});

test('le résumé exige d’avoir passé l’écran 6', function (): void {
    [, $onboarding] = freshParent();
    walkOnboarding($onboarding, 5);

    $this->get(route('onboarding.summary', $onboarding))
        ->assertRedirect(route('onboarding.preferences', $onboarding));
});

test('corriger une section depuis le résumé y ramène', function (string $route, array $data): void {
    $onboarding = onboardingAtSummary();

    $this->put(route($route, $onboarding), $data)->assertRedirect(route('onboarding.summary', $onboarding));
})->with([
    'profil' => ['onboarding.child.update', childData(['grade' => '5e'])],
    'passions' => ['onboarding.interests.update', ['interests' => ['lego'], 'custom' => []]],
    'objectifs' => ['onboarding.goals.update', ['goals' => ['autonomy'], 'primary' => 'autonomy']],
    'difficultés' => ['onboarding.difficulties.update', ['difficulties' => []]],
    'préférences' => ['onboarding.preferences.update', ['session_minutes' => 5]],
]);

test('la correction est visible dans le résumé', function (): void {
    $onboarding = onboardingAtSummary();

    $this->put(route('onboarding.interests.update', $onboarding), ['interests' => ['lego'], 'custom' => []]);

    $this->get(route('onboarding.summary', $onboarding))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('summary.interests', ['lego'])
            ->where('summary.customInterests', []));
});

test('« Découvrir mon espace » termine l’onboarding', function (): void {
    $onboarding = onboardingAtSummary();

    $this->put(route('onboarding.summary.update', $onboarding))->assertRedirect(route('dashboard'));

    expect($onboarding->fresh()?->isCompleted())->toBeTrue();
});

test('« Ajouter un autre enfant » termine l’onboarding et en ouvre un nouveau', function (): void {
    $onboarding = onboardingAtSummary();

    $response = $this->put(route('onboarding.summary.update', $onboarding), ['add_child' => true]);

    $second = Onboarding::query()->latest('id')->first();
    expect($second?->id)->not->toBe($onboarding->id)
        ->and($second?->child_profile_id)->toBeNull()
        ->and($onboarding->fresh()?->isCompleted())->toBeTrue();
    $response->assertRedirect(route('onboarding.child', $second));

    $this->get(route('onboarding.child', $second))
        ->assertInertia(fn (Assert $page): Assert => $page->where('child', null));
    expect(ChildProfile::count())->toBe(1);
});

test('l’adresse générique d’un écran renvoie vers sa page', function (): void {
    $onboarding = onboardingAtSummary();

    $this->get(route('onboarding.step', [$onboarding, 7]))->assertRedirect(route('onboarding.summary', $onboarding));
    $this->get(route('onboarding.step', [$onboarding, 5]))->assertRedirect(route('onboarding.difficulties', $onboarding));
});
