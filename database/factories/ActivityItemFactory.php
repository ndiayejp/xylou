<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Activities\Enums\AnswerType;
use App\Domain\Activities\Models\Activity;
use App\Domain\Activities\Models\ActivityItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityItem>
 */
class ActivityItemFactory extends Factory
{
    public function definition(): array
    {
        $a = fake()->numberBetween(2, 9);
        $b = fake()->numberBetween(2, 9);

        return [
            'activity_id' => Activity::factory(),
            'position' => 1,
            'prompt' => "Combien font {$a} × {$b} ?",
            'answer_type' => AnswerType::Number,
            'expected_answer' => ['value' => $a * $b],
            'hint' => 'Pense aux tables de multiplication.',
        ];
    }
}
