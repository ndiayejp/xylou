<?php

declare(strict_types=1);

namespace App\Domain\Activities\Actions;

use App\Domain\Activities\Enums\ActivityLogEvent;
use App\Domain\Activities\Models\Activity;
use App\Domain\Activities\Support\ActivityJournal;
use App\Domain\Identity\Models\User;

// Mise à la corbeille, quel que soit le statut : restaurable 30 jours (RestoreActivity).
final readonly class DeleteActivity
{
    public function __construct(private ActivityJournal $journal) {}

    public function __invoke(Activity $activity, User $actor): void
    {
        $activity->delete();

        $this->journal->record(ActivityLogEvent::Deleted, $activity, $actor);
    }
}
