<?php

declare(strict_types=1);

use App\Domain\Children\Models\ChildProfile;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Gate;

// Les acteurs sont créés dans le test (fermetures), l'enfant appartient toujours à `owner`.
function actor(string $who, User $owner): ?User
{
    return match ($who) {
        'propriétaire' => $owner,
        'autre parent' => User::factory()->parent()->create(),
        'pro' => User::factory()->professional()->withTwoFactor()->create(),
        'admin' => User::factory()->admin()->create(),
        'sans rôle' => User::factory()->create(),
        'visiteur' => null,
    };
}

test('seul le parent propriétaire voit et gère le profil', function (string $who, bool $allowed): void {
    $owner = User::factory()->parent()->create();
    $child = ChildProfile::factory()->for($owner, 'owner')->create();
    $user = actor($who, $owner);

    foreach (['view', 'update', 'delete'] as $ability) {
        expect(Gate::forUser($user)->allows($ability, $child))
            ->toBe($allowed, "{$ability} pour {$who}");
    }
})->with([
    ['propriétaire', true],
    ['autre parent', false],
    ['pro', false],
    ['admin', false],
    ['sans rôle', false],
    ['visiteur', false],
]);

test('seul un parent peut créer un profil enfant', function (string $who, bool $allowed): void {
    $user = actor($who, User::factory()->parent()->create());

    expect(Gate::forUser($user)->allows('create', ChildProfile::class))->toBe($allowed);
})->with([
    ['propriétaire', true],
    ['pro', false],
    ['admin', false],
    ['sans rôle', false],
    ['visiteur', false],
]);

test('un profil supprimé n’est plus accessible, même à son parent', function (): void {
    $owner = User::factory()->parent()->create();
    $child = ChildProfile::factory()->for($owner, 'owner')->create();
    $child->delete();

    expect(ChildProfile::query()->ownedBy($owner)->count())->toBe(0);
});
