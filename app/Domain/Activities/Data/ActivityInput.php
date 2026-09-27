<?php

declare(strict_types=1);

namespace App\Domain\Activities\Data;

use App\Domain\Activities\Enums\ActivityDifficulty;
use App\Domain\Activities\Enums\ActivityFormat;
use App\Domain\Activities\Exceptions\ActivityRuleViolation;
use App\Domain\Activities\Models\Activity;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Curriculum\Models\Skill;
use App\Domain\Curriculum\Models\Universe;
use App\Domain\Identity\Models\User;

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

    // Règles communes à la création et à la modification.
    public function ensureValidFor(User $owner): void
    {
        if ($this->skill->retired_at !== null || $this->skill->isDomain()) {
            throw ActivityRuleViolation::inactiveSkill();
        }

        if (! in_array($this->durationMinutes, Activity::DURATIONS, true)) {
            throw ActivityRuleViolation::invalidDuration($this->durationMinutes);
        }

        if ($this->child instanceof ChildProfile && ! $this->child->isOwnedBy($owner)) {
            throw ActivityRuleViolation::foreignChild();
        }
    }

    /** @return array<string, mixed> champs de l'activité (hors auteur, statut, source) */
    public function attributes(): array
    {
        return [
            'title' => $this->title,
            'format' => $this->format,
            'difficulty' => $this->difficulty,
            'duration_minutes' => $this->durationMinutes,
            'learning_objective' => $this->learningObjective,
        ];
    }
}
