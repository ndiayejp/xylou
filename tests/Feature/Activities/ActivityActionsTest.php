<?php

declare(strict_types=1);

use App\Domain\Activities\Actions\ArchiveActivity;
use App\Domain\Activities\Actions\ChangeActivityStatus;
use App\Domain\Activities\Actions\CreateActivity;
use App\Domain\Activities\Actions\DeleteActivity;
use App\Domain\Activities\Actions\DuplicateActivity;
use App\Domain\Activities\Actions\RestoreActivity;
use App\Domain\Activities\Actions\UnarchiveActivity;
use App\Domain\Activities\Data\ActivityInput;
use App\Domain\Activities\Data\ActivityItemInput;
use App\Domain\Activities\Enums\ActivityDifficulty;
use App\Domain\Activities\Enums\ActivityFormat;
use App\Domain\Activities\Enums\ActivitySource;
use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Enums\AnswerType;
use App\Domain\Activities\Exceptions\ActivityRuleViolation;
use App\Domain\Activities\Models\Activity;
use App\Domain\Activities\Models\ActivityItem;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Curriculum\Models\Skill;
use App\Domain\Identity\Models\User;
use Spatie\Activitylog\Models\Activity as LogEntry;

/** @return list<array<string, mixed>> */
function activityLog(): array
{
    return LogEntry::query()->where('log_name', 'activities')->orderBy('id')->get()
        ->map(fn (LogEntry $entry): array => ['event' => $entry->event, ...$entry->properties->all()])
        ->all();
}

function activityInput(array $overrides = []): ActivityInput
{
    return new ActivityInput(...[
        'title' => 'Le voyage de la fusée',
        'skill' => Skill::factory()->create(),
        'format' => ActivityFormat::Problem,
        'difficulty' => ActivityDifficulty::Practice,
        'durationMinutes' => 10,
        'items' => [
            new ActivityItemInput('La fusée parcourt 240 km en 3 minutes. Combien en 1 minute ?', AnswerType::Number, ['value' => 80], hint: 'Partage en 3.'),
            new ActivityItemInput('Et en 2 minutes ?', AnswerType::Number, ['value' => 160]),
        ],
        ...$overrides,
    ]);
}

describe('création', function (): void {
    test('une activité manuelle naît en brouillon, avec ses items dans l’ordre', function (): void {
        $parent = User::factory()->parent()->create();
        $input = activityInput();

        $activity = resolve(CreateActivity::class)($parent, $input);

        expect($activity->status)->toBe(ActivityStatus::Draft)
            ->and($activity->source)->toBe(ActivitySource::Manual)
            ->and($activity->owner_id)->toBe($parent->id)
            ->and($activity->subject_id)->toBe($input->skill->subject_id)
            ->and($activity->child_profile_id)->toBeNull()
            ->and($activity->version)->toBe(1)
            ->and(strlen($activity->id))->toBe(26)
            ->and($activity->items->pluck('position')->all())->toBe([1, 2])
            ->and($activity->items->first()?->expected_answer)->toBe(['value' => 80]);

        expect(activityLog())->toBe([['event' => 'created', 'activity' => $activity->id]]);
    });

    test('elle peut être destinée à un enfant du parent, pas à celui d’un autre', function (): void {
        $parent = User::factory()->parent()->create();
        $own = ChildProfile::factory()->for($parent, 'owner')->create();

        $activity = resolve(CreateActivity::class)($parent, activityInput(['child' => $own]));
        expect($activity->child_profile_id)->toBe($own->id);

        resolve(CreateActivity::class)($parent, activityInput(['child' => ChildProfile::factory()->create()]));
    })->throws(ActivityRuleViolation::class);

    test('la compétence doit être en vigueur et travaillée en activité', function (Skill $skill): void {
        resolve(CreateActivity::class)(User::factory()->parent()->create(), activityInput(['skill' => $skill]));
    })->with([
        'retirée' => fn (): Skill => Skill::factory()->retired()->create(),
        'domaine' => fn (): Skill => Skill::factory()->domain()->create(),
    ])->throws(ActivityRuleViolation::class);

    test('la durée est de 5, 10, 15 ou 20 minutes', function (): void {
        resolve(CreateActivity::class)(User::factory()->parent()->create(), activityInput(['durationMinutes' => 12]));
    })->throws(ActivityRuleViolation::class, '12');
});

describe('transitions', function (): void {
    test('chaque transition interdite est refusée, sans rien changer ni journaliser', function (ActivityStatus $from, ActivityStatus $to): void {
        $activity = Activity::factory()->status($from)->create();

        expect(fn () => resolve(ChangeActivityStatus::class)($activity, $to, $activity->owner))
            ->toThrow(ActivityRuleViolation::class);

        expect($activity->fresh()?->status)->toBe($from)
            ->and(activityLog())->toBe([]);
    })->with(fn (): array => collect(ActivityStatus::cases())
        ->crossJoin(ActivityStatus::cases())
        ->reject(fn (array $pair): bool => $pair[0]->canTransitionTo($pair[1]))
        ->mapWithKeys(fn (array $pair): array => ["{$pair[0]->value} → {$pair[1]->value}" => $pair])
        ->all());

    test('une transition autorisée est journalisée avec les deux statuts', function (): void {
        $activity = Activity::factory()->status(ActivityStatus::Draft)->create();

        resolve(ChangeActivityStatus::class)($activity, ActivityStatus::Approved, $activity->owner);

        expect($activity->fresh()?->status)->toBe(ActivityStatus::Approved)
            ->and(activityLog())->toBe([
                ['event' => 'status_changed', 'activity' => $activity->id, 'from' => 'draft', 'to' => 'approved'],
            ])
            ->and(LogEntry::query()->first()?->causer_id)->toBe($activity->owner_id);
    });

    test('une activité sans question ne peut être ni validée ni soumise', function (ActivityStatus $to): void {
        $activity = Activity::factory()->status(ActivityStatus::Draft)->withoutItems()->create();

        resolve(ChangeActivityStatus::class)($activity, $to, $activity->owner);
    })->with([ActivityStatus::Approved, ActivityStatus::PendingReview])->throws(ActivityRuleViolation::class);

    test('archiver puis désarchiver une activité approuvée', function (): void {
        $activity = Activity::factory()->create();

        resolve(ArchiveActivity::class)($activity, $activity->owner);
        expect($activity->status)->toBe(ActivityStatus::Archived)
            ->and($activity->archived_at)->not->toBeNull();

        resolve(UnarchiveActivity::class)($activity, $activity->owner);
        expect($activity->fresh()?->status)->toBe(ActivityStatus::Approved)
            ->and($activity->fresh()?->archived_at)->toBeNull();
    });

    test('seule une activité approuvée s’archive', function (ActivityStatus $status): void {
        $activity = Activity::factory()->status($status)->create();

        resolve(ArchiveActivity::class)($activity, $activity->owner);
    })->with([ActivityStatus::Draft, ActivityStatus::PendingReview, ActivityStatus::Generating])
        ->throws(ActivityRuleViolation::class);

    test('seule une activité archivée se désarchive', function (): void {
        $activity = Activity::factory()->status(ActivityStatus::Draft)->create();

        resolve(UnarchiveActivity::class)($activity, $activity->owner);
    })->throws(ActivityRuleViolation::class);
});

describe('duplication', function (): void {
    test('la copie est un brouillon de bibliothèque à celui qui duplique, items compris', function (): void {
        $parent = User::factory()->parent()->create();
        $original = Activity::factory()->for($parent, 'owner')->create([
            'child_profile_id' => ChildProfile::factory()->for($parent, 'owner'),
            'reviewed_by' => $parent->id,
            'reviewed_at' => now(),
            'version' => 3,
        ]);

        $copy = resolve(DuplicateActivity::class)($original, $parent);

        expect($copy->id)->not->toBe($original->id)
            ->and($copy->status)->toBe(ActivityStatus::Draft)
            ->and($copy->source)->toBe(ActivitySource::Duplicate)
            ->and($copy->owner_id)->toBe($parent->id)
            ->and($copy->child_profile_id)->toBeNull()
            ->and($copy->reviewed_by)->toBeNull()
            ->and($copy->version)->toBe(1)
            ->and($copy->title)->toBe($original->title)
            ->and($copy->skill_id)->toBe($original->skill_id)
            ->and($copy->items->pluck('prompt')->all())->toBe($original->items->pluck('prompt')->all())
            ->and($copy->items->pluck('position')->all())->toBe([1, 2, 3])
            ->and(ActivityItem::query()->count())->toBe(6);

        expect(activityLog())->toBe([['event' => 'duplicated', 'activity' => $copy->id, 'from' => $original->id]]);
    });

    test('une activité sans contenu ne se duplique pas', function (ActivityStatus $status): void {
        $activity = Activity::factory()->status($status)->create();

        resolve(DuplicateActivity::class)($activity, $activity->owner);
    })->with([ActivityStatus::Generating, ActivityStatus::GenerationFailed])->throws(ActivityRuleViolation::class);
});

describe('corbeille', function (): void {
    test('supprimer puis annuler : l’activité revient avec son statut', function (): void {
        $activity = Activity::factory()->status(ActivityStatus::Archived)->create();

        resolve(DeleteActivity::class)($activity, $activity->owner);
        expect(Activity::query()->find($activity->id))->toBeNull();

        $trashed = Activity::withTrashed()->findOrFail($activity->id);
        resolve(RestoreActivity::class)($trashed, $activity->owner);

        expect(Activity::query()->find($activity->id)?->status)->toBe(ActivityStatus::Archived)
            ->and(array_column(activityLog(), 'event'))->toBe(['deleted', 'restored']);
    });

    test('une activité n’est plus restaurable après 30 jours', function (): void {
        $activity = Activity::factory()->create();
        resolve(DeleteActivity::class)($activity, $activity->owner);

        $this->travel(30)->days();

        resolve(RestoreActivity::class)(Activity::withTrashed()->findOrFail($activity->id), $activity->owner);
    })->throws(ActivityRuleViolation::class);

    test('une activité qui n’est pas dans la corbeille ne se restaure pas', function (): void {
        $activity = Activity::factory()->create();

        resolve(RestoreActivity::class)($activity, $activity->owner);
    })->throws(ActivityRuleViolation::class);

    test('après 30 jours, l’activité et ses items sont effacés', function (): void {
        $old = Activity::factory()->create();
        $recent = Activity::factory()->create();
        $kept = Activity::factory()->create();

        resolve(DeleteActivity::class)($old, $old->owner);
        $this->travel(20)->days();
        resolve(DeleteActivity::class)($recent, $recent->owner);
        $this->travel(11)->days();

        $this->artisan('model:prune', ['--model' => [Activity::class]])->assertSuccessful();

        expect(Activity::withTrashed()->pluck('id')->sort()->values()->all())
            ->toBe(collect([$recent->id, $kept->id])->sort()->values()->all())
            ->and(ActivityItem::query()->where('activity_id', $old->id)->exists())->toBeFalse();
    });
});

describe('autorisations', function (): void {
    test('parents et professionnels créent des activités', function (): void {
        expect(User::factory()->parent()->create()->can('create', Activity::class))->toBeTrue()
            ->and(User::factory()->professional()->create()->can('create', Activity::class))->toBeTrue()
            ->and(User::factory()->create()->can('create', Activity::class))->toBeFalse();
    });

    test('seul l’auteur gère son activité', function (string $ability): void {
        $activity = Activity::factory()->create();

        expect($activity->owner->can($ability, $activity))->toBeTrue()
            ->and(User::factory()->parent()->create()->can($ability, $activity))->toBeFalse();
    })->with(['view', 'update', 'duplicate', 'archive', 'delete', 'restore']);
});

test('le journal ne garde ni le contenu de l’activité ni le prénom de l’enfant', function (): void {
    $parent = User::factory()->parent()->create();
    $child = ChildProfile::factory()->for($parent, 'owner')->create(['first_name' => 'Lucas']);

    $activity = resolve(CreateActivity::class)($parent, activityInput(['child' => $child, 'title' => 'La mission de Lucas']));
    resolve(ChangeActivityStatus::class)($activity, ActivityStatus::Approved, $parent);
    resolve(DuplicateActivity::class)($activity, $parent);
    resolve(DeleteActivity::class)($activity, $parent);

    $journal = LogEntry::query()->where('log_name', 'activities')->get()->toJson();

    expect($journal)->not->toContain('Lucas')
        ->not->toContain('240 km')
        ->not->toContain($parent->email);
});
