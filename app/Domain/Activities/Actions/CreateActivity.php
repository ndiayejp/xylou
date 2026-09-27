<?php

declare(strict_types=1);

namespace App\Domain\Activities\Actions;

use App\Domain\Activities\Data\ActivityInput;
use App\Domain\Activities\Enums\ActivityLogEvent;
use App\Domain\Activities\Enums\ActivitySource;
use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Exceptions\ActivityRuleViolation;
use App\Domain\Activities\Models\Activity;
use App\Domain\Activities\Support\ActivityJournal;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;

// Activité écrite par l'adulte : créée en brouillon, approuvée quand il l'enregistre (éditeur, PR 5).
final readonly class CreateActivity
{
    public function __construct(private ActivityJournal $journal) {}

    public function __invoke(User $owner, ActivityInput $input): Activity
    {
        $skill = $input->skill;

        if ($skill->retired_at !== null || $skill->isDomain()) {
            throw ActivityRuleViolation::inactiveSkill();
        }

        if (! in_array($input->durationMinutes, Activity::DURATIONS, true)) {
            throw ActivityRuleViolation::invalidDuration($input->durationMinutes);
        }

        if ($input->child instanceof ChildProfile && ! $input->child->isOwnedBy($owner)) {
            throw ActivityRuleViolation::foreignChild();
        }

        return DB::transaction(function () use ($owner, $input, $skill): Activity {
            $activity = new Activity([
                'title' => $input->title,
                'format' => $input->format,
                'difficulty' => $input->difficulty,
                'duration_minutes' => $input->durationMinutes,
                'status' => ActivityStatus::Draft,
                'source' => ActivitySource::Manual,
                'learning_objective' => $input->learningObjective,
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

            return $activity;
        });
    }
}
