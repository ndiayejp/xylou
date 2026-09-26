<?php

declare(strict_types=1);

use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role as RoleModel;

function userWithRole(?Role $role): User
{
    return match ($role) {
        Role::Parent => User::factory()->parent()->create(),
        Role::Professional => User::factory()->professional()->withTwoFactor()->create(),
        Role::Admin => User::factory()->admin()->create(),
        null => User::factory()->create(),
    };
}

test('chaque rôle existe en base', function (): void {
    expect(RoleModel::pluck('name')->sort()->values()->all())
        ->toBe(collect(Role::cases())->map->value->sort()->values()->all());
});

test('chaque rôle n’accède qu’à son espace', function (?Role $role, string $space, int $status): void {
    $this->actingAs(userWithRole($role))
        ->get(route($space))
        ->assertStatus($status);
})->with([
    'parent → espace parent' => [Role::Parent, 'parent.dashboard', 200],
    'parent → espace pro' => [Role::Parent, 'pro.dashboard', 403],
    'pro → espace pro' => [Role::Professional, 'pro.dashboard', 200],
    'pro → espace parent' => [Role::Professional, 'parent.dashboard', 403],
    'admin → espace parent' => [Role::Admin, 'parent.dashboard', 403],
    'admin → espace pro' => [Role::Admin, 'pro.dashboard', 403],
    'sans rôle → espace parent' => [null, 'parent.dashboard', 403],
    'sans rôle → espace pro' => [null, 'pro.dashboard', 403],
]);

test('un visiteur est renvoyé vers la connexion', function (string $space): void {
    $this->get(route($space))->assertRedirect(route('login'));
})->with(['parent.dashboard', 'pro.dashboard', 'dashboard']);

test('une adresse non vérifiée ne donne pas accès à l’espace', function (): void {
    $this->actingAs(User::factory()->parent()->unverified()->create())
        ->get(route('parent.dashboard'))
        ->assertRedirect(route('verification.notice'));
});

test('« dashboard » aiguille chaque compte vers son espace', function (Role $role, string $space): void {
    $this->actingAs(userWithRole($role))
        ->get(route('dashboard'))
        ->assertRedirect(route($space));
})->with([
    [Role::Parent, 'parent.dashboard'],
    [Role::Professional, 'pro.dashboard'],
]);

test('un compte sans espace est refusé', function (?Role $role): void {
    $this->actingAs(userWithRole($role))
        ->get(route('dashboard'))
        ->assertForbidden();
})->with([Role::Admin, null]);

test('les pages reçoivent les rôles de l’utilisateur', function (): void {
    $this->actingAs(User::factory()->parent()->create(['name' => 'Sophie Martin']))
        ->get(route('parent.dashboard'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Parent/Dashboard/Index')
            ->where('auth.user.name', 'Sophie Martin')
            ->where('auth.user.roles', ['parent'])
            ->missing('auth.user.password'));
});

test('l’espace pro rend son tableau de bord', function (): void {
    $this->actingAs(User::factory()->professional()->withTwoFactor()->create())
        ->get(route('pro.dashboard'))
        ->assertInertia(fn (Assert $page): Assert => $page->component('Pro/Dashboard/Index'));
});
