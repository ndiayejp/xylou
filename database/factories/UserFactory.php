<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    // 2FA active et confirmée ; codes de secours connus pour les tests.
    public function withTwoFactor(string $secret = 'JBSWY3DPEHPK3PXP'): static
    {
        return $this->state(fn (array $attributes): array => [
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode(['code-secours-1', 'code-secours-2'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function parent(): static
    {
        return $this->withRole(Role::Parent);
    }

    public function professional(): static
    {
        return $this->withRole(Role::Professional);
    }

    public function admin(): static
    {
        return $this->withRole(Role::Admin);
    }

    private function withRole(Role $role): static
    {
        return $this->afterCreating(fn (User $user): User => $user->assignRole($role->value));
    }
}
