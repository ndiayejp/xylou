<?php

declare(strict_types=1);

namespace App\Domain\Activities\Support;

use App\Domain\Activities\Enums\ActivityLogEvent;
use App\Domain\Activities\Models\Activity;
use App\Domain\Identity\Models\User;

// Journal « activities » (§6.3). L'ULID de l'activité va dans les propriétés : la colonne
// « subject_id » du journal attend un entier.
final class ActivityJournal
{
    /** @param array<string, string> $details */
    public function record(ActivityLogEvent $event, Activity $activity, User $causer, array $details = []): void
    {
        activity(ActivityLogEvent::LOG_NAME)
            ->event($event->value)
            ->causedBy($causer)
            ->withProperties(['activity' => $activity->id, ...$details])
            ->log($event->value);
    }
}
