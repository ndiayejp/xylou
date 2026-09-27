<?php

declare(strict_types=1);

namespace App\Domain\Activities\Data;

use App\Domain\Activities\Enums\ActivityDifficulty;
use App\Domain\Activities\Enums\ActivityFormat;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Curriculum\Models\Skill;
use App\Domain\Curriculum\Models\Universe;

// Saisie d'une activité manuelle. spatie/laravel-data n'est pas encore installé : simple objet immuable.
final readonly class ActivityInput
{
    /** @param list<ActivityItemInput> $items */
    public function __construct(
        public string $title,
        public Skill $skill,
        public ActivityFormat $format,
        public ActivityDifficulty $difficulty,
        public int $durationMinutes,
        public ?Universe $universe = null,
        public ?ChildProfile $child = null,
        public ?string $learningObjective = null,
        public array $items = [],
    ) {}
}
