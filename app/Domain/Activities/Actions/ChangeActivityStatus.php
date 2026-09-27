<?php

declare(strict_types=1);

namespace App\Domain\Activities\Actions;

use App\Domain\Activities\Enums\ActivityLogEvent;
use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Exceptions\ActivityRuleViolation;
use App\Domain\Activities\Models\Activity;
use App\Domain\Activities\Support\ActivityJournal;
use App\Domain\Identity\Models\User;

// Seul point de passage d'un changement de statut : refuse les transitions interdites et les journalise.
final readonly class ChangeActivityStatus
{
    public function __construct(private ActivityJournal $journal) {}

    public function __invoke(Activity $activity, ActivityStatus $to, User $actor): Activity
    {
        $from = $activity->status;

        if (! $from->canTransitionTo($to)) {
            throw ActivityRuleViolation::transition($from, $to);
        }

        if (in_array($to, [ActivityStatus::PendingReview, ActivityStatus::Approved], true) && ! $activity->items()->exists()) {
            throw ActivityRuleViolation::withoutItems();
        }

        $activity->status = $to;
        $activity->archived_at = $to === ActivityStatus::Archived ? now() : null;
        $activity->save();

        $this->journal->record(ActivityLogEvent::StatusChanged, $activity, $actor, [
            'from' => $from->value,
            'to' => $to->value,
        ]);

        return $activity;
    }
}
