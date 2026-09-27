<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Curriculum\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Pour les tests qui n'ont pas besoin du référentiel complet (SyncCurriculum).
 *
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->lexify('subject-????'),
            'color_token' => 'maths',
            'icon_key' => 'calculator',
            'position' => fake()->numberBetween(1, 100),
        ];
    }
}
