<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Children\Enums\Grade;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChildProfile>
 */
class ChildProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->parent(),
            'first_name' => fake()->firstName(),
            'birth_year' => fake()->numberBetween(2011, 2020),
            'grade' => fake()->randomElement(Grade::cases()),
        ];
    }
}
