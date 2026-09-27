<?php

declare(strict_types=1);

namespace App\Domain\Activities\Actions;

use App\Domain\Activities\Data\ActivityInput;
use App\Domain\Activities\Enums\ActivityLogEvent;
use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Exceptions\ActivityRuleViolation;
use App\Domain\Activities\Models\Activity;
use App\Domain\Activities\Support\ActivityJournal;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;

// Modification par l'adulte : champs et questions remplacés, version suivante. Une activité validée
// reste validée, donc complète ; « $approve » valide un brouillon dans la même opération.
final readonly class UpdateActivity
{
    public function __construct(private ActivityJournal $journal, private ChangeActivityStatus $changeStatus) {}

    public function __invoke(Activity $activity, ActivityInput $input, User $actor, bool $approve = false): Activity
    {
        if (! $activity->status->isEditable()) {
            throw ActivityRuleViolation::notEditable($activity->status);
        }

        $input->ensureValidFor($activity->owner);

        return DB::transaction(function () use ($activity, $input, $actor, $approve): Activity {
            $activity->fill($input->attributes());
            $activity->skill()->associate($input->skill);
            $activity->subject_id = $input->skill->subject_id;
            $activity->universe()->associate($input->universe);
            $activity->version++;
            $activity->save();

            $activity->items()->delete();
            foreach ($input->items as $index => $item) {
                $activity->items()->create($item->attributes($index + 1));
            }
            $activity->unsetRelation('items');

            $this->journal->record(ActivityLogEvent::Updated, $activity, $actor, ['version' => (string) $activity->version]);

            if ($approve && $activity->status === ActivityStatus::Draft) {
                ($this->changeStatus)($activity, ActivityStatus::Approved, $actor);
            } elseif ($activity->status === ActivityStatus::Approved) {
                $activity->ensureReady();
            }

            return $activity;
        });
    }
}
