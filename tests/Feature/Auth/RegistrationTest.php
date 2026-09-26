<?php

declare(strict_types=1);

use App\Domain\Children\Models\Onboarding;
use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Privacy\Enums\ConsentKind;
use App\Domain\Privacy\Models\Consent;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;

function accountData(array $overrides = []): array
{
    return [
        'first_name' => 'Sophie',
        'last_name' => 'Bernard',
        'email' => 'sophie@example.com',
        'password' => 'une phrase facile à retenir',
        'parental_authority' => true,
        'terms' => true,
        ...$overrides,
    ];
}

test('l’écran 1 « Votre compte » s’affiche', function (): void {
    $this->get('/register')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Onboarding/Account'));
});

test('l’inscription crée un parent, envoie la vérification et ouvre l’écran 2', function (): void {
    Event::fake([Registered::class]);

    $response = $this->post('/register', accountData());

    $user = User::firstWhere('email', 'sophie@example.com');
    $onboarding = Onboarding::query()->sole();

    expect($user)
        ->name->toBe('Sophie Bernard')
        ->and($user?->getRoleNames()->all())->toBe([Role::Parent->value])
        ->and($onboarding->parent_id)->toBe($user?->id)
        ->and($onboarding->reached_step)->toBe(2);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('onboarding.child', $onboarding));
    Event::assertDispatched(Registered::class);
});

test('les deux consentements sont horodatés, versionnés, avec une IP hachée', function (): void {
    $this->post('/register', accountData());

    $consents = Consent::all();

    expect($consents->pluck('kind')->all())->toEqualCanonicalizing(ConsentKind::cases())
        ->and($consents->every(fn (Consent $consent): bool => $consent->granted
            && $consent->version === config('xylou.consent_version')))->toBeTrue()
        ->and($consents->first()?->ip_hash)->toHaveLength(64)
        ->not->toBe('127.0.0.1');
});

test('sans consentement, pas de compte', function (string $missing): void {
    $this->post('/register', accountData([$missing => false]))
        ->assertSessionHasErrors($missing);

    $this->assertGuest();
    expect(User::count())->toBe(0);
})->with(['parental_authority', 'terms']);

test('un mot de passe de moins de 12 caractères est refusé', function (): void {
    $this->post('/register', accountData(['password' => 'court123']))->assertSessionHasErrors('password');

    $this->assertGuest();
});

test('une adresse déjà utilisée est refusée', function (): void {
    User::factory()->create(['email' => 'sophie@example.com']);

    $this->post('/register', accountData())->assertSessionHasErrors('email');
});
