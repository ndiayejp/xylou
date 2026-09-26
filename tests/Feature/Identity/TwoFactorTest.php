<?php

declare(strict_types=1);

use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use PragmaRX\Google2FA\Google2FA;

const SECRET = 'JBSWY3DPEHPK3PXP';

function currentCode(string $secret = SECRET): string
{
    return resolve(Google2FA::class)->getCurrentOtp($secret);
}

function confirmedPassword(): array
{
    return ['auth.password_confirmed_at' => time()];
}

describe('connexion avec 2FA', function (): void {
    test('le code est demandé avant d’ouvrir la session', function (): void {
        $user = User::factory()->parent()->withTwoFactor()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('two-factor.login'))
            ->assertSessionHas('login.id', $user->id);

        $this->assertGuest();
    });

    test('sans 2FA, la connexion est directe', function (): void {
        $user = User::factory()->parent()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    });

    test('la page du code exige des identifiants déjà validés', function (): void {
        $this->get(route('two-factor.login'))->assertRedirect(route('login'));
    });

    test('la page du code s’affiche après les identifiants', function (): void {
        $user = User::factory()->parent()->withTwoFactor()->create();

        $this->withSession(['login.id' => $user->id])
            ->get(route('two-factor.login'))
            ->assertInertia(fn (Assert $page): Assert => $page->component('Auth/TwoFactorChallenge'));
    });

    test('un code valide ouvre la session', function (): void {
        $user = User::factory()->parent()->withTwoFactor()->create();

        $this->withSession(['login.id' => $user->id, 'login.remember' => false])
            ->post(route('two-factor.login.store'), ['code' => currentCode()])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    });

    test('un code faux est refusé', function (): void {
        $user = User::factory()->parent()->withTwoFactor()->create();

        $this->withSession(['login.id' => $user->id])
            ->post(route('two-factor.login.store'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    });

    test('un code de secours ouvre la session une seule fois', function (): void {
        $user = User::factory()->parent()->withTwoFactor()->create();

        $this->withSession(['login.id' => $user->id])
            ->post(route('two-factor.login.store'), ['recovery_code' => 'code-secours-1'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        expect($user->fresh()?->recoveryCodes())->not->toContain('code-secours-1');
    });
});

describe('2FA obligatoire pour les professionnels', function (): void {
    test('un pro sans 2FA est renvoyé vers la page Sécurité', function (): void {
        $this->actingAs(User::factory()->professional()->create())
            ->get(route('pro.dashboard'))
            ->assertRedirect(route('settings.security'));
    });

    test('un pro avec 2FA accède à son espace', function (): void {
        $this->actingAs(User::factory()->professional()->withTwoFactor()->create())
            ->get(route('pro.dashboard'))
            ->assertOk();
    });

    test('un parent sans 2FA accède à son espace', function (): void {
        $this->actingAs(User::factory()->parent()->create())
            ->get(route('parent.dashboard'))
            ->assertOk();
    });

    test('un pro ne peut pas désactiver sa 2FA', function (): void {
        $pro = User::factory()->professional()->withTwoFactor()->create();

        $this->actingAs($pro)
            ->withSession(confirmedPassword())
            ->delete(route('two-factor.disable'))
            ->assertForbidden();

        expect($pro->fresh()?->hasEnabledTwoFactorAuthentication())->toBeTrue();
    });
});

describe('page Sécurité', function (): void {
    test('elle exige de confirmer le mot de passe', function (): void {
        $this->actingAs(User::factory()->parent()->create())
            ->get(route('settings.security'))
            ->assertRedirect(route('password.confirm'));
    });

    test('un visiteur est renvoyé vers la connexion', function (): void {
        $this->get(route('settings.security'))->assertRedirect(route('login'));
    });

    test('activation : QR code et clé, puis confirmation par un code', function (): void {
        $user = User::factory()->parent()->create();

        $this->actingAs($user)->withSession(confirmedPassword())
            ->post(route('two-factor.enable'))
            ->assertRedirect();

        $this->get(route('settings.security'))
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Settings/Security')
                ->where('twoFactor.enabled', false)
                ->where('twoFactor.pending', true)
                ->where('twoFactor.required', false)
                ->has('twoFactor.qrCodeSvg')
                ->has('twoFactor.setupKey'));

        $secret = decrypt((string) $user->fresh()?->two_factor_secret);

        $this->post(route('two-factor.confirm'), ['code' => currentCode($secret)])
            ->assertRedirect();

        expect($user->fresh()?->hasEnabledTwoFactorAuthentication())->toBeTrue();

        $this->get(route('settings.security'))
            ->assertInertia(fn (Assert $page): Assert => $page
                ->where('twoFactor.enabled', true)
                ->where('twoFactor.qrCodeSvg', null)
                ->has('twoFactor.recoveryCodes', 8));
    });

    test('un code de confirmation faux ne l’active pas', function (): void {
        $user = User::factory()->parent()->create();

        $this->actingAs($user)->withSession(confirmedPassword())
            ->post(route('two-factor.enable'));

        $this->post(route('two-factor.confirm'), ['code' => '000000'])
            ->assertSessionHasErrors('code', errorBag: 'confirmTwoFactorAuthentication');

        expect($user->fresh()?->hasEnabledTwoFactorAuthentication())->toBeFalse();
    });

    test('un parent peut désactiver sa 2FA', function (): void {
        $user = User::factory()->parent()->withTwoFactor()->create();

        $this->actingAs($user)->withSession(confirmedPassword())
            ->delete(route('two-factor.disable'))
            ->assertRedirect();

        expect($user->fresh()?->two_factor_secret)->toBeNull();
    });

    test('les codes de secours peuvent être régénérés', function (): void {
        $user = User::factory()->parent()->withTwoFactor()->create();

        $this->actingAs($user)->withSession(confirmedPassword())
            ->post(route('two-factor.regenerate-recovery-codes'))
            ->assertRedirect();

        expect($user->fresh()?->recoveryCodes())
            ->toHaveCount(8)
            ->not->toContain('code-secours-1');
    });
});

test('juste après la connexion, la page Sécurité ne redemande pas le mot de passe', function (): void {
    $pro = User::factory()->professional()->create();

    $this->post('/login', ['email' => $pro->email, 'password' => 'password']);

    $this->get(route('pro.dashboard'))->assertRedirect(route('settings.security'));
    $this->get(route('settings.security'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->where('twoFactor.required', true));
});
