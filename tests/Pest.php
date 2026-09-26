<?php

declare(strict_types=1);

use App\Domain\Children\Models\Onboarding;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', fn () => $this->toBe(1));

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

// Parent tout juste inscrit (adresse non vérifiée) avec un onboarding à l'écran 2.
function freshParent(): array
{
    $parent = User::factory()->parent()->unverified()->create();
    test()->actingAs($parent)->post(route('onboarding.start'));

    return [$parent, Onboarding::query()->where('parent_id', $parent->id)->sole()];
}

function childData(array $overrides = []): array
{
    return [
        'first_name' => 'Lucas',
        'age' => 11,
        'grade' => '6e',
        'language' => 'fr',
        'avatar' => 'ball',
        'read_aloud' => true,
        'dyslexia_font' => false,
        'no_timer' => true,
        ...$overrides,
    ];
}

// Valide les écrans 2 à $upTo par leurs vraies routes, avec des réponses valides.
function walkOnboarding(Onboarding $onboarding, int $upTo): void
{
    $steps = [
        2 => fn () => test()->put(route('onboarding.child.update', $onboarding), childData()),
        3 => fn () => test()->put(route('onboarding.interests.update', $onboarding), ['interests' => ['football'], 'custom' => []]),
        4 => fn () => test()->put(route('onboarding.goals.update', $onboarding), ['goals' => ['regain_confidence'], 'primary' => 'regain_confidence']),
        5 => fn () => test()->put(route('onboarding.difficulties.update', $onboarding), ['difficulties' => []]),
        6 => fn () => test()->put(route('onboarding.preferences.update', $onboarding), ['session_minutes' => 10]),
        7 => fn () => test()->put(route('onboarding.summary.update', $onboarding), ['add_child' => false]),
    ];

    foreach (range(2, $upTo) as $step) {
        $steps[$step]();
    }
}
