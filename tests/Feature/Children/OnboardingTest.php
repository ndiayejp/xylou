<?php

declare(strict_types=1);

use App\Domain\Children\Enums\AvatarKey;
use App\Domain\Children\Enums\Grade;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Children\Models\Onboarding;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

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

describe('écran 2 : profil de l’enfant', function (): void {
    test('un parent non vérifié peut décrire son enfant', function (): void {
        [, $onboarding] = freshParent();

        $this->get(route('onboarding.child', $onboarding))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Onboarding/ChildProfile')
                ->where('child', null));
    });

    test('le profil est créé, l’âge devient une année de naissance, l’écran 3 s’ouvre', function (): void {
        [$parent, $onboarding] = freshParent();

        $this->put(route('onboarding.child.update', $onboarding), childData())
            ->assertRedirect(route('onboarding.step', [$onboarding, 3]));

        $child = ChildProfile::query()->sole();
        expect($child)
            ->owner_id->toBe($parent->id)
            ->first_name->toBe('Lucas')
            ->grade->toBe(Grade::Sixieme)
            ->birth_year->toBe(now()->year - 11)
            ->avatar_key->toBe(AvatarKey::Ball)
            ->and($child->comfort('read_aloud'))->toBeTrue()
            ->and($child->comfort('no_timer'))->toBeTrue()
            ->and($onboarding->fresh())
            ->child_profile_id->toBe($child->id)
            ->reached_step->toBe(3);
    });

    test('revenir à l’écran 2 retrouve les réponses, et les modifier ne crée pas de doublon', function (): void {
        [, $onboarding] = freshParent();
        $this->put(route('onboarding.child.update', $onboarding), childData());

        $this->get(route('onboarding.child', $onboarding))
            ->assertInertia(fn (Assert $page): Assert => $page
                ->where('child.firstName', 'Lucas')
                ->where('child.age', 11)
                ->where('child.avatar', 'ball')
                ->where('child.readAloud', true));

        $this->put(route('onboarding.child.update', $onboarding), childData(['first_name' => 'Luca', 'age' => null]));

        expect(ChildProfile::count())->toBe(1)
            ->and(ChildProfile::query()->sole())->first_name->toBe('Luca')->birth_year->toBeNull()
            ->and($onboarding->fresh()?->reached_step)->toBe(3);
    });

    test('les champs sont validés', function (array $overrides, string $field): void {
        [, $onboarding] = freshParent();

        $this->put(route('onboarding.child.update', $onboarding), childData($overrides))
            ->assertSessionHasErrors($field);
    })->with([
        'prénom vide' => [['first_name' => ''], 'first_name'],
        'âge trop petit' => [['age' => 3], 'age'],
        'classe inconnue' => [['grade' => 'terminale'], 'grade'],
        'avatar inconnu' => [['avatar' => 'photo'], 'avatar'],
        'langue non prise en charge' => [['language' => 'xx'], 'language'],
    ]);
});

describe('progression et reprise', function (): void {
    test('on ne peut pas sauter une étape', function (): void {
        [, $onboarding] = freshParent();

        $this->get(route('onboarding.step', [$onboarding, 3]))
            ->assertRedirect(route('onboarding.child', $onboarding));
        $this->post(route('onboarding.step.next', [$onboarding, 5]))
            ->assertRedirect(route('onboarding.child', $onboarding));
    });

    test('le parcours va jusqu’au bout, puis l’espace parent exige la vérification', function (): void {
        [$parent, $onboarding] = freshParent();
        $this->put(route('onboarding.child.update', $onboarding), childData());

        foreach ([3, 4, 5, 6] as $step) {
            $this->post(route('onboarding.step.next', [$onboarding, $step]))
                ->assertRedirect(route('onboarding.step', [$onboarding, $step + 1]));
        }
        $this->post(route('onboarding.step.next', [$onboarding, 7]))->assertRedirect(route('dashboard'));

        expect($onboarding->fresh()?->isCompleted())->toBeTrue();
        $this->get(route('dashboard'))->assertRedirect(route('verification.notice'));

        $parent->markEmailAsVerified();
        $this->get(route('dashboard'))->assertRedirect(route('parent.dashboard'));
    });

    test('après une nouvelle connexion, « dashboard » reprend là où on s’était arrêté', function (): void {
        [$parent, $onboarding] = freshParent();
        $this->put(route('onboarding.child.update', $onboarding), childData());
        $this->post(route('onboarding.step.next', [$onboarding, 3]));

        $this->post('/logout');
        $this->post('/login', ['email' => $parent->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->get(route('dashboard'))->assertRedirect(route('onboarding.step', [$onboarding, 4]));
    });

    test('ajouter un enfant ouvre un nouvel onboarding, sans toucher au premier', function (): void {
        [$parent, $first] = freshParent();
        $this->put(route('onboarding.child.update', $first), childData());
        foreach ([3, 4, 5, 6, 7] as $step) {
            $this->post(route('onboarding.step.next', [$first, $step]));
        }

        $this->post(route('onboarding.start'));

        $second = Onboarding::query()->where('parent_id', $parent->id)->latest('id')->first();
        expect($second?->id)->not->toBe($first->id)
            ->and($second?->reached_step)->toBe(2)
            ->and($first->fresh()?->isCompleted())->toBeTrue();
    });

    test('démarrer deux fois sans avoir décrit l’enfant réutilise le même onboarding', function (): void {
        [$parent] = freshParent();

        $this->post(route('onboarding.start'));

        expect(Onboarding::query()->where('parent_id', $parent->id)->count())->toBe(1);
    });
});

describe('accès', function (): void {
    test('l’onboarding d’un autre parent est refusé', function (): void {
        [, $onboarding] = freshParent();

        $this->actingAs(User::factory()->parent()->create())
            ->get(route('onboarding.child', $onboarding))
            ->assertForbidden();
        $this->put(route('onboarding.child.update', $onboarding), childData())->assertForbidden();
    });

    test('réservé aux parents connectés', function (): void {
        [, $onboarding] = freshParent();

        $this->actingAs(User::factory()->professional()->withTwoFactor()->create())
            ->post(route('onboarding.start'))
            ->assertForbidden();

        $this->post('/logout');
        $this->get(route('onboarding.child', $onboarding))->assertRedirect(route('login'));
    });
});
