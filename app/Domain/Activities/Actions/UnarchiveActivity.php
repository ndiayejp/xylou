<?php

declare(strict_types=1);

namespace App\Domain\Activities\Actions;

use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Exceptions\ActivityRuleViolation;
use App\Domain\Activities\Models\Activity;
use App\Domain\Identity\Models\User;

// Sortie des archives : l'activité redevient approuvée.
final readonly class UnarchiveActivity
{
    public function __construct(private ChangeActivityStatus $changeStatus) {}

    public function __invoke(Activity $activity, User $actor): Activity
    {
        // Sans cette garde, un brouillon serait approuvé (draft → approved est permis).
        if ($activity->status !== ActivityStatus::Archived) {
            throw ActivityRuleViolation::transition($activity->status, ActivityStatus::Approved);
        }

        return ($this->changeStatus)($activity, ActivityStatus::Approved, $actor);
    }
}
