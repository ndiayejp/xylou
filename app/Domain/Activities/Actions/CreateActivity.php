<?php

declare(strict_types=1);

namespace App\Domain\Activities\Actions;

use App\Domain\Activities\Data\ActivityInput;
use App\Domain\Activities\Enums\ActivityLogEvent;
use App\Domain\Activities\Enums\ActivitySource;
use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Models\Activity;
use App\Domain\Activities\Support\ActivityJournal;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;

// Activité écrite par l'adulte : créée en brouillon ; « $approve » la valide dans la même opération.
final readonly class CreateActivity
{
    public function __construct(private ActivityJournal $journal, private ChangeActivityStatus $changeStatus) {}

    public function __invoke(User $owner, ActivityInput $input, bool $approve = false): Activity
    {
        $input->ensureValidFor($owner);
        $skill = $input->skill;

        return DB::transaction(function () use ($owner, $input, $skill, $approve): Activity {
            $activity = new Activity([
                ...$input->attributes(),
                'status' => ActivityStatus::Draft,
                'source' => ActivitySource::Manual,
            ]);
            $activity->owner()->associate($owner);
            $activity->skill()->associate($skill);
            // La matière suit toujours la compétence.
            $activity->subject_id = $skill->subject_id;
            $activity->universe()->associate($input->universe);
            $activity->childProfile()->associate($input->child);
            $activity->save();

            foreach ($input->items as $index => $item) {
                $activity->items()->create($item->attributes($index + 1));
            }

            $this->journal->record(ActivityLogEvent::Created, $activity, $owner);

            if ($approve) {
                ($this->changeStatus)($activity, ActivityStatus::Approved, $owner);
            }

            return $activity;
        });
    }
}
