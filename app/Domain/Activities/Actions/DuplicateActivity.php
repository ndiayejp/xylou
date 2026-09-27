<?php

declare(strict_types=1);

namespace App\Domain\Activities\Actions;

use App\Domain\Activities\Enums\ActivityLogEvent;
use App\Domain\Activities\Enums\ActivitySource;
use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Exceptions\ActivityRuleViolation;
use App\Domain\Activities\Models\Activity;
use App\Domain\Activities\Support\ActivityJournal;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;

// La copie, items compris, est un brouillon de bibliothèque à celui qui duplique.
final readonly class DuplicateActivity
{
    public function __construct(private ActivityJournal $journal) {}

    public function __invoke(Activity $original, User $actor): Activity
    {
        if (! $original->status->hasContent()) {
            throw ActivityRuleViolation::notDuplicable($original->status);
        }

        return DB::transaction(function () use ($original, $actor): Activity {
            $copy = $original->replicate(['owner_id', 'child_profile_id', 'reviewed_by', 'reviewed_at', 'archived_at', 'deleted_at']);
            $copy->status = ActivityStatus::Draft;
            $copy->source = ActivitySource::Duplicate;
            $copy->version = 1;
            $copy->owner()->associate($actor);
            $copy->save();

            foreach ($original->items as $item) {
                $copy->items()->save($item->replicate(['activity_id']));
            }

            $this->journal->record(ActivityLogEvent::Duplicated, $copy, $actor, ['from' => $original->id]);

            return $copy;
        });
    }
}
