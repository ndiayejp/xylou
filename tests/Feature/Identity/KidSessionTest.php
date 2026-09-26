<?php

declare(strict_types=1);

use App\Domain\Children\Models\ChildProfile;
use App\Domain\Identity\Models\User;
use Illuminate\Routing\Route as RouteDefinition;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

// Ouvre la session enfant comme le ferait le parent, et renvoie [parent, enfant].
function openKidSession(): array
{
    $parent = User::factory()->parent()->create();
    $child = ChildProfile::factory()->for($parent, 'owner')->create(['first_name' => 'Emma']);

    test()->actingAs($parent)
        ->post(route('parent.children.kid-session', $child))
        ->assertRedirect(route('kid.home'));

    return [$parent, $child];
}

describe('ouverture', function (): void {
    test('le parent ouvre l’espace de son enfant : sa session se ferme', function (): void {
        [, $child] = openKidSession();

        expect(auth('kid')->id())->toBe($child->id);
        $this->assertGuest('web');

        $this->get(route('kid.home'))
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Kid/Home/Index')
                ->where('kid', ['id' => $child->id, 'firstName' => 'Emma'])
                ->where('auth.user', null)
                ->where('parent', null));
    });

    test('un parent ne peut pas ouvrir l’espace d’un enfant qui n’est pas le sien', function (): void {
        $other = ChildProfile::factory()->create();

        $this->actingAs(User::factory()->parent()->create())
            ->post(route('parent.children.kid-session', $other))
            ->assertForbidden();

        $this->assertGuest('kid');
    });

    test('un pro ou un visiteur ne peut pas ouvrir de session enfant', function (): void {
        $child = ChildProfile::factory()->create();

        $this->actingAs(User::factory()->professional()->withTwoFactor()->create())
            ->post(route('parent.children.kid-session', $child))
            ->assertForbidden();

        $this->post('/logout');
        $this->post(route('parent.children.kid-session', $child))->assertRedirect(route('login'));
        $this->assertGuest('kid');
    });

    test('sans session enfant, l’espace enfant renvoie vers la connexion', function (): void {
        $this->get(route('kid.home'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->parent()->create())
            ->get(route('kid.home'))
            ->assertRedirect(route('login'));
    });
});

describe('invariant : un enfant n’atteint jamais une route hors de son espace', function (): void {
    test('toutes les pages de l’application renvoient vers l’espace enfant', function (): void {
        openKidSession();

        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn (RouteDefinition $route): bool => in_array('GET', $route->methods(), true)
                && in_array('web', $route->gatherMiddleware(), true)
                && ! str_starts_with((string) $route->getName(), 'kid.'))
            ->map(fn (RouteDefinition $route): string => preg_replace('/\{[^}]+\}/', '1', $route->uri()) ?? $route->uri());

        expect($routes)->not->toBeEmpty();

        foreach ($routes as $uri) {
            $this->get('/'.ltrim($uri, '/'))->assertRedirect(route('kid.home'));
        }
    });

    test('les actions de l’espace parent et du compte sont aussi bloquées', function (string $method, string $uri): void {
        [, $child] = openKidSession();

        $this->call($method, str_replace('{child}', (string) $child->id, $uri))
            ->assertRedirect(route('kid.home'));

        expect(auth('kid')->check())->toBeTrue();
    })->with([
        ['POST', '/logout'],
        ['POST', '/parent/current-child'],
        ['POST', '/parent/children/{child}/kid-session'],
        ['PATCH', '/profile'],
        ['DELETE', '/profile'],
        ['PUT', '/settings/parent-pin'],
        ['POST', '/login'],
    ]);
});

describe('sortie par le code parent', function (): void {
    test('sans PIN, le mot de passe du parent ferme la session enfant', function (): void {
        [$parent] = openKidSession();

        $this->get(route('kid.exit'))
            ->assertInertia(fn (Assert $page): Assert => $page->component('Kid/Exit')->where('usesPin', false));

        $this->post(route('kid.exit.store'), ['code' => 'password'])
            ->assertRedirect(route('parent.dashboard'));

        $this->assertAuthenticatedAs($parent, 'web');
        $this->assertGuest('kid');
    });

    test('avec un PIN, seul le PIN est accepté', function (): void {
        [$parent] = openKidSession();
        $parent->forceFill(['parent_pin' => '4821'])->save();

        $this->post(route('kid.exit.store'), ['code' => 'password'])->assertSessionHasErrors('code');
        expect(auth('kid')->check())->toBeTrue();

        $this->post(route('kid.exit.store'), ['code' => '4821'])->assertRedirect(route('parent.dashboard'));
        $this->assertAuthenticatedAs($parent, 'web');
    });

    test('un code faux laisse l’enfant dans son espace', function (): void {
        openKidSession();

        $this->post(route('kid.exit.store'), ['code' => '0000'])
            ->assertSessionHasErrors(['code' => 'Ce code ne correspond pas.']);

        expect(auth('kid')->check())->toBeTrue();
        $this->assertGuest('web');
    });

    test('après 5 essais, la sortie est bloquée même avec le bon code', function (): void {
        openKidSession();

        foreach (range(1, 5) as $attempt) {
            $this->post(route('kid.exit.store'), ['code' => '0000']);
        }

        $this->post(route('kid.exit.store'), ['code' => 'password'])->assertSessionHasErrors('code');

        expect(session('errors')->first('code'))->toContain('Trop d’essais');
        expect(auth('kid')->check())->toBeTrue();
    });
});

describe('PIN parent', function (): void {
    test('un parent définit un PIN de 4 à 6 chiffres, stocké haché', function (): void {
        $parent = User::factory()->parent()->create();

        $this->actingAs($parent)->withSession(['auth.password_confirmed_at' => time()])
            ->put(route('parent-pin.update'), ['pin' => '4821', 'pin_confirmation' => '4821'])
            ->assertSessionHas('status', 'parent-pin-updated');

        expect($parent->fresh()?->parent_pin)->not->toBe('4821')
            ->and($parent->fresh()?->checkParentCode('4821'))->toBeTrue();
    });

    test('un PIN invalide est refusé', function (string $pin): void {
        $this->actingAs(User::factory()->parent()->create())
            ->withSession(['auth.password_confirmed_at' => time()])
            ->put(route('parent-pin.update'), ['pin' => $pin, 'pin_confirmation' => $pin])
            ->assertSessionHasErrors('pin');
    })->with(['123', '1234567', 'abcd', '12a4']);

    test('supprimer le PIN rend le mot de passe au rôle de code parent', function (): void {
        $parent = User::factory()->parent()->create(['parent_pin' => '4821']);

        $this->actingAs($parent)->withSession(['auth.password_confirmed_at' => time()])
            ->delete(route('parent-pin.destroy'));

        expect($parent->fresh()?->hasParentPin())->toBeFalse()
            ->and($parent->fresh()?->checkParentCode('password'))->toBeTrue();
    });

    test('le PIN est réservé aux parents et exige la confirmation du mot de passe', function (): void {
        $this->actingAs(User::factory()->professional()->withTwoFactor()->create())
            ->withSession(['auth.password_confirmed_at' => time()])
            ->put(route('parent-pin.update'), ['pin' => '4821', 'pin_confirmation' => '4821'])
            ->assertForbidden();

        $this->actingAs(User::factory()->parent()->create())
            ->withSession(['auth.password_confirmed_at' => null])
            ->put(route('parent-pin.update'), ['pin' => '4821', 'pin_confirmation' => '4821'])
            ->assertRedirect(route('password.confirm'));
    });
});
