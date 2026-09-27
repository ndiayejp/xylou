<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Activities\Enums\ActivityDifficulty;
use App\Domain\Activities\Enums\ActivityFormat;
use App\Domain\Activities\Enums\ActivitySource;
use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Models\Activity;
use App\Domain\Activities\Models\ActivityItem;
use App\Domain\Curriculum\Models\Skill;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Activité approuvée de bibliothèque, avec 3 items.
 *
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->parent(),
            'skill_id' => Skill::factory(),
            'subject_id' => fn (array $attributes): int => Skill::query()->whereKey($attributes['skill_id'])->firstOrFail()->subject_id,
            'title' => fake()->sentence(3),
            'format' => ActivityFormat::Exercise,
            'difficulty' => ActivityDifficulty::Practice,
            'duration_minutes' => 10,
            'status' => ActivityStatus::Approved,
            'source' => ActivitySource::Manual,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Activity $activity): void {
            if ($activity->status->hasContent() && ! $activity->items()->exists()) {
                ActivityItem::factory()->count(3)->sequence(fn ($sequence): array => ['position' => $sequence->index + 1])
                    ->for($activity)->create();
            }
        });
    }

    public function status(ActivityStatus $status): static
    {
        return $this->state([
            'status' => $status,
            'archived_at' => $status === ActivityStatus::Archived ? now() : null,
        ]);
    }

    public function withoutItems(): static
    {
        return $this->afterCreating(fn (Activity $activity) => $activity->items()->delete());
    }
}
