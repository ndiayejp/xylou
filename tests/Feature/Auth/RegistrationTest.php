<?php

declare(strict_types=1);

use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

test('registration screen can be rendered', function (): void {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function (): void {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'une phrase facile à retenir',
        'password_confirmation' => 'une phrase facile à retenir',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('l’inscription publique crée un compte parent', function (): void {
    Event::fake([Registered::class]);

    $this->post('/register', [
        'name' => 'Sophie Martin',
        'email' => 'sophie@example.com',
        'password' => 'une phrase facile à retenir',
        'password_confirmation' => 'une phrase facile à retenir',
    ]);

    $user = User::firstWhere('email', 'sophie@example.com');

    expect($user?->getRoleNames()->all())->toBe([Role::Parent->value]);
    Event::assertDispatched(Registered::class);
});

test('un mot de passe de moins de 12 caractères est refusé', function (): void {
    $this->post('/register', [
        'name' => 'Sophie Martin',
        'email' => 'sophie@example.com',
        'password' => 'court123',
        'password_confirmation' => 'court123',
    ])->assertSessionHasErrors('password');

    $this->assertGuest();
});
