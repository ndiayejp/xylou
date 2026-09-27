<?php

declare(strict_types=1);

namespace App\Domain\Activities\Actions;

use App\Domain\Activities\Enums\ActivityLogEvent;
use App\Domain\Activities\Exceptions\ActivityRuleViolation;
use App\Domain\Activities\Models\Activity;
use App\Domain\Activities\Support\ActivityJournal;
use App\Domain\Identity\Models\User;

// Sortie de la corbeille (« Annuler » après suppression) : l'activité retrouve son statut.
final readonly class RestoreActivity
{
    public function __construct(private ActivityJournal $journal) {}

    public function __invoke(Activity $activity, User $actor): Activity
    {
        if (! $activity->isRestorable()) {
            throw ActivityRuleViolation::notRestorable();
        }

        $activity->restore();

        $this->journal->record(ActivityLogEvent::Restored, $activity, $actor);

        return $activity;
    }
}
