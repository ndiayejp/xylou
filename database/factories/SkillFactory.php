<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Curriculum\Enums\Grade;
use App\Domain\Curriculum\Models\Skill;
use App\Domain\Curriculum\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Compétence travaillée en activité, rattachée à un domaine de sa matière.
 *
 * @extends Factory<Skill>
 */
class SkillFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'parent_skill_id' => fn (array $attributes): int => Skill::query()->create([
                'subject_id' => $attributes['subject_id'],
                'code' => fake()->unique()->lexify('TEST.DOMAIN.????'),
                'label' => 'Domaine',
                'position' => 1,
            ])->id,
            'code' => fake()->unique()->lexify('TEST.CE2.SKILL.????'),
            'label' => fake()->sentence(4),
            'grade_min' => Grade::Ce2,
            'grade_max' => Grade::Ce2,
            'position' => fake()->numberBetween(1, 100),
        ];
    }

    // Domaine : compétence sans parent ni niveau, qu'on ne travaille pas directement.
    public function domain(): static
    {
        return $this->state(['parent_skill_id' => null, 'grade_min' => null, 'grade_max' => null]);
    }

    public function retired(): static
    {
        return $this->state(['retired_at' => now()]);
    }
}
