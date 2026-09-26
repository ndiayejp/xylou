<?php

declare(strict_types=1);

use App\Domain\Children\Models\ChildProfile;
use App\Domain\Identity\Models\User;
use PragmaRX\Google2FA\Google2FA;
use Spatie\Activitylog\Models\Activity;

function securityEvents(): array
{
    return Activity::query()->where('log_name', 'security')->orderBy('id')->pluck('event')->all();
}

// Aucune donnée personnelle dans le journal (§11.4).
function expectNoPersonalData(string ...$values): void
{
    $journal = Activity::all()->toJson();

    foreach ($values as $value) {
        expect($journal)->not->toContain($value);
    }
}

function confirmed(): array
{
    return ['auth.password_confirmed_at' => time()];
}

describe('connexion', function (): void {
    test('connexion et déconnexion sont journalisées, avec l’auteur', function (): void {
        $user = User::factory()->parent()->create(['name' => 'Sophie Martin', 'email' => 'sophie@example.com']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post('/logout');

        expect(securityEvents())->toBe(['login', 'logout']);
        expect(Activity::query()->where('event', 'login')->first()?->causer_id)->toBe($user->id);
        expectNoPersonalData('Sophie Martin', 'sophie@example.com');
    });

    test('un échec sur un compte inconnu ne garde pas l’e-mail saisi', function (): void {
        $this->post('/login', ['email' => 'inconnu@example.com', 'password' => 'mauvais-mot-de-passe']);

        expect(securityEvents())->toBe(['login_failed']);
        expect(Activity::query()->first()?->causer_id)->toBeNull();
        expectNoPersonalData('inconnu@example.com');
    });

    test('le blocage après 5 échecs est journalisé', function (): void {
        $user = User::factory()->create();

        foreach (range(1, 6) as $attempt) {
            $this->post('/login', ['email' => $user->email, 'password' => 'mauvais-mot-de-passe']);
        }

        expect(securityEvents())->toContain('lockout');
    });

    test('la suppression de compte garde seulement l’identifiant', function (): void {
        $user = User::factory()->parent()->create(['email' => 'sophie@example.com']);

        $this->actingAs($user)->delete('/profile', ['password' => 'password']);

        $entry = Activity::query()->where('event', 'account_deleted')->first();
        expect($entry?->properties->get('user_id'))->toBe($user->id);
        expectNoPersonalData('sophie@example.com');
    });
});

describe('double authentification', function (): void {
    test('activation, confirmation, codes de secours et désactivation', function (): void {
        $user = User::factory()->parent()->create();
        $this->actingAs($user)->withSession(confirmed());

        $this->post(route('two-factor.enable'));
        $secret = decrypt((string) $user->fresh()?->two_factor_secret);
        $this->post(route('two-factor.confirm'), ['code' => resolve(Google2FA::class)->getCurrentOtp($secret)]);
        $this->post(route('two-factor.regenerate-recovery-codes'));
        $this->delete(route('two-factor.disable'));

        expect(securityEvents())->toBe([
            'two_factor_enabled',
            'two_factor_confirmed',
            'recovery_codes_regenerated',
            'two_factor_disabled',
        ]);
    });

    test('code refusé, puis code de secours utilisé à la connexion', function (): void {
        $user = User::factory()->parent()->withTwoFactor()->create();

        $this->withSession(['login.id' => $user->id])
            ->post(route('two-factor.login.store'), ['code' => '000000']);
        $this->withSession(['login.id' => $user->id])
            ->post(route('two-factor.login.store'), ['recovery_code' => 'code-secours-1']);

        expect(securityEvents())->toBe(['two_factor_failed', 'recovery_code_used', 'two_factor_passed', 'login']);
    });
});

describe('session enfant', function (): void {
    test('ouverture, code refusé, puis sortie, avec l’enfant concerné', function (): void {
        $parent = User::factory()->parent()->create(['email' => 'sophie@example.com']);
        $child = ChildProfile::factory()->for($parent, 'owner')->create(['first_name' => 'Emma']);

        $this->actingAs($parent)->post(route('parent.children.kid-session', $child));
        $this->post(route('kid.exit.store'), ['code' => '0000']);
        $this->post(route('kid.exit.store'), ['code' => 'password']);

        expect(securityEvents())->toBe([
            'logout',
            'kid_session_opened',
            'parent_code_rejected',
            'login',
            'kid_session_closed',
        ]);

        $opened = Activity::query()->where('event', 'kid_session_opened')->first();
        expect($opened)
            ->causer_id->toBe($parent->id)
            ->subject_id->toBe($child->id)
            ->subject_type->toBe($child->getMorphClass());
        expectNoPersonalData('Emma', 'sophie@example.com');
    });

    test('le 5e code refusé est journalisé comme un blocage', function (): void {
        $parent = User::factory()->parent()->create();
        $child = ChildProfile::factory()->for($parent, 'owner')->create();

        $this->actingAs($parent)->post(route('parent.children.kid-session', $child));
        foreach (range(1, 5) as $attempt) {
            $this->post(route('kid.exit.store'), ['code' => '0000']);
        }

        expect(array_count_values(securityEvents()))
            ->toMatchArray(['parent_code_rejected' => 4, 'parent_code_locked' => 1]);
    });

    test('définir puis retirer le PIN parent', function (): void {
        $parent = User::factory()->parent()->create();
        $this->actingAs($parent)->withSession(confirmed());

        $this->put(route('parent-pin.update'), ['pin' => '4821', 'pin_confirmation' => '4821']);
        $this->delete(route('parent-pin.destroy'));

        expect(securityEvents())->toBe(['parent_pin_updated', 'parent_pin_removed']);
        expectNoPersonalData('4821');
    });
});

test('les profils enfants sont journalisés sans aucune valeur de champ', function (): void {
    $child = ChildProfile::factory()->create(['first_name' => 'Emma', 'birth_year' => 2018]);
    $child->update(['first_name' => 'Emmanuelle']);
    $child->delete();

    expect(Activity::query()->where('log_name', 'children')->pluck('event')->all())
        ->toBe(['created', 'updated', 'deleted']);
    expectNoPersonalData('Emma', 'Emmanuelle', '2018');
});
