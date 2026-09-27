<?php

declare(strict_types=1);

use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Enums\AnswerType;
use App\Domain\Activities\Models\Activity;
use App\Domain\Activities\Models\ActivityItem;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Curriculum\Enums\Grade;
use App\Domain\Curriculum\Models\Skill;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Activitylog\Models\Activity as LogEntry;

/** Questions complètes, une par type de réponse. */
function editorItems(): array
{
    return [
        ['answer_type' => 'number', 'prompt' => '4 caisses de 6 bouteilles : combien ?', 'value' => '24', 'unit' => 'bouteilles', 'tolerance' => '', 'explanation' => '4 × 6 = 24.', 'hint' => 'Multiplie.'],
        ['answer_type' => 'text', 'prompt' => 'Quelle planète est rouge ?', 'accepted' => ['Mars', ' mars ', ''], 'explanation' => 'Mars est la planète rouge.'],
        ['answer_type' => 'single_choice', 'prompt' => '6 × 7 ?', 'choices' => ['42', '48'], 'correct' => 0, 'explanation' => '6 × 7 = 42.'],
        ['answer_type' => 'multiple_choice', 'prompt' => 'Les nombres pairs ?', 'choices' => ['2', '3', '4'], 'correct_many' => [2, 0], 'explanation' => '2 et 4 se divisent par 2.'],
    ];
}

function editorPayload(array $overrides = []): array
{
    return [
        'intent' => 'approve',
        'title' => 'Le ravitaillement de la fusée',
        'skill_id' => Skill::factory()->create()->id,
        'universe' => 'space',
        'format' => 'problem',
        'difficulty' => 'practice',
        'duration_minutes' => 10,
        'learning_objective' => 'Résoudre un problème en 2 étapes',
        'items' => editorItems(),
        ...$overrides,
    ];
}

describe('création', function (): void {
    test('la page propose les compétences en vigueur et part de la classe de l’enfant', function (): void {
        $parent = User::factory()->parent()->create();
        ChildProfile::factory()->for($parent, 'owner')->create(['grade' => Grade::Cm1]);
        $skill = Skill::factory()->create();
        Skill::factory()->retired()->create();

        $this->actingAs($parent)->get(route('activities.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Activities/Edit')
                ->where('activity', null)
                ->where('defaultGrade', 'cm1')
                ->where('options.skills', [[
                    'id' => $skill->id,
                    'label' => $skill->label,
                    'subject' => $skill->subject->key,
                    'grades' => ['ce2'],
                ]])
                ->where('options.answerTypes', ['number', 'text', 'single_choice', 'multiple_choice'])
                ->where('libraryUrl', route('parent.library')));
    });

    test('enregistrer et valider : les quatre types de réponse sont rangés dans leur forme', function (): void {
        $parent = User::factory()->parent()->create();

        $this->actingAs($parent)->post(route('activities.store'), editorPayload())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('parent.library'));

        $activity = Activity::query()->sole();
        expect($activity->status)->toBe(ActivityStatus::Approved)
            ->and($activity->owner_id)->toBe($parent->id)
            ->and($activity->child_profile_id)->toBeNull()
            ->and($activity->universe?->key)->toBe('space');

        [$number, $text, $single, $multiple] = $activity->items->all();
        expect($number->answer_type)->toBe(AnswerType::Number)
            // jsonb (PostgreSQL) range les clés à sa façon : on compare sans l'ordre.
            ->and($number->expected_answer)->toEqual(['value' => 24, 'unit' => 'bouteilles'])
            ->and($number->tolerance)->toBeNull()
            ->and($number->hint)->toBe('Multiplie.')
            ->and($text->expected_answer)->toBe(['accepted' => ['Mars', 'mars']])
            ->and($single->prompt_payload)->toBe(['choices' => ['42', '48']])
            ->and($single->expected_answer)->toBe(['index' => 0])
            ->and($multiple->expected_answer)->toBe(['indexes' => [0, 2]])
            ->and($activity->items->every->isComplete())->toBeTrue();
    });

    test('un brouillon peut rester incomplet', function (): void {
        $parent = User::factory()->parent()->create();

        $this->actingAs($parent)->post(route('activities.store'), editorPayload([
            'intent' => 'draft',
            'universe' => null,
            'items' => [['answer_type' => 'single_choice', 'prompt' => '', 'choices' => ['', '']]],
        ]))->assertSessionHasNoErrors();

        $activity = Activity::query()->sole();
        expect($activity->status)->toBe(ActivityStatus::Draft)
            ->and($activity->items->sole()->isComplete())->toBeFalse();
    });

    test('pour valider, chaque manque est signalé sur son champ, tout d’un coup', function (): void {
        $this->actingAs(User::factory()->parent()->create())
            ->post(route('activities.store'), editorPayload(['title' => '', 'items' => [
                ['answer_type' => 'number', 'prompt' => ' ', 'value' => ''],
                ['answer_type' => 'text', 'prompt' => 'Q', 'explanation' => 'E', 'accepted' => ['', ' ']],
                ['answer_type' => 'single_choice', 'prompt' => 'Q', 'explanation' => 'E', 'choices' => ['A', '']],
                ['answer_type' => 'single_choice', 'prompt' => 'Q', 'explanation' => 'E', 'choices' => ['A', 'B'], 'correct' => 2],
                ['answer_type' => 'multiple_choice', 'prompt' => 'Q', 'explanation' => 'E', 'choices' => ['A', 'B'], 'correct_many' => []],
            ]]))
            ->assertSessionHasErrors([
                'title',
                'items.0.prompt' => __('activities.editor.prompt'),
                'items.0.explanation', 'items.0.value',
                'items.1.accepted', 'items.2.choices', 'items.3.correct', 'items.4.correct_many',
            ]);

        expect(Activity::query()->count())->toBe(0);
    });

    test('pour valider, il faut au moins une question', function (): void {
        $this->actingAs(User::factory()->parent()->create())
            ->post(route('activities.store'), editorPayload(['items' => []]))
            ->assertSessionHasErrors('items');
    });

    test('la compétence doit être en vigueur et travaillée en activité', function (Skill $skill): void {
        $this->actingAs(User::factory()->parent()->create())
            ->post(route('activities.store'), editorPayload(['skill_id' => $skill->id]))
            ->assertSessionHasErrors('skill_id');
    })->with([
        'retirée' => fn (): Skill => Skill::factory()->retired()->create(),
        'domaine' => fn (): Skill => Skill::factory()->domain()->create(),
    ]);

    test('les champs de l’activité sont vérifiés', function (): void {
        $this->actingAs(User::factory()->parent()->create())
            ->post(route('activities.store'), editorPayload([
                'title' => '', 'format' => 'poeme', 'duration_minutes' => 12, 'universe' => 'mer',
                'items' => [['answer_type' => 'ordering']],
            ]))
            ->assertSessionHasErrors(['title', 'format', 'duration_minutes', 'universe', 'items.0.answer_type']);
    });

    test('un pro crée aussi ses activités, rangées dans sa bibliothèque', function (): void {
        $pro = User::factory()->professional()->withTwoFactor()->create();

        $this->actingAs($pro)->post(route('activities.store'), editorPayload())
            ->assertRedirect(route('pro.library'));

        expect(Activity::query()->sole()->owner_id)->toBe($pro->id);
    });
});

describe('modification', function (): void {
    test('la page reprend l’activité et ses questions à plat', function (): void {
        $parent = User::factory()->parent()->create();
        $this->actingAs($parent)->post(route('activities.store'), editorPayload());
        $activity = Activity::query()->sole();

        $this->get(route('activities.edit', $activity))->assertInertia(fn (Assert $page): Assert => $page
            ->where('activity.id', $activity->id)
            ->where('activity.status', 'approved')
            ->where('activity.grade', 'ce2')
            ->where('activity.items.0.value', 24)
            ->where('activity.items.0.unit', 'bouteilles')
            ->where('activity.items.1.accepted', ['Mars', 'mars'])
            ->where('activity.items.2.choices', ['42', '48'])
            ->where('activity.items.2.correct', 0)
            ->where('activity.items.3.correct_many', [0, 2]));
    });

    test('un brouillon se complète puis se valide ; la version et le journal suivent', function (): void {
        $parent = User::factory()->parent()->create();
        $draft = Activity::factory()->for($parent, 'owner')->status(ActivityStatus::Draft)->create();

        $this->actingAs($parent)->put(route('activities.update', $draft), editorPayload(['title' => 'Nouveau titre']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('parent.library'));

        $draft->refresh();
        expect($draft->title)->toBe('Nouveau titre')
            ->and($draft->status)->toBe(ActivityStatus::Approved)
            ->and($draft->version)->toBe(2)
            ->and($draft->items()->count())->toBe(4)
            ->and(ActivityItem::query()->count())->toBe(4)
            ->and(LogEntry::query()->where('log_name', 'activities')->pluck('event')->all())
            ->toBe(['updated', 'status_changed']);
    });

    test('un brouillon enregistré reste un brouillon', function (): void {
        $parent = User::factory()->parent()->create();
        $draft = Activity::factory()->for($parent, 'owner')->status(ActivityStatus::Draft)->create();

        $this->actingAs($parent)->put(route('activities.update', $draft), editorPayload(['intent' => 'draft']));

        expect($draft->fresh()?->status)->toBe(ActivityStatus::Draft);
    });

    test('une activité validée reste complète, même enregistrée « en brouillon »', function (): void {
        $parent = User::factory()->parent()->create();
        $approved = Activity::factory()->for($parent, 'owner')->create();

        $this->actingAs($parent)->put(route('activities.update', $approved), editorPayload([
            'intent' => 'draft',
            'items' => [['answer_type' => 'number', 'prompt' => 'Q']],
        ]))->assertSessionHasErrors(['items.0.explanation', 'items.0.value']);

        expect($approved->fresh()?->status)->toBe(ActivityStatus::Approved)
            ->and($approved->fresh()?->version)->toBe(1);
    });

    test('seul l’auteur modifie un brouillon ou une activité validée', function (User $user, ActivityStatus $status, int $expected): void {
        $activity = Activity::factory()->status($status)->create();
        $user = $user->exists ? $user : $activity->owner;

        $this->actingAs($user)->get(route('activities.edit', $activity))->assertStatus($expected);
        $this->put(route('activities.update', $activity), editorPayload())->assertStatus($expected === 200 ? 302 : $expected);
    })->with([
        'autre parent' => [fn (): User => User::factory()->parent()->create(), ActivityStatus::Approved, 403],
        'archivée' => [fn (): User => new User, ActivityStatus::Archived, 403],
        'à valider' => [fn (): User => new User, ActivityStatus::PendingReview, 403],
        'brouillon' => [fn (): User => new User, ActivityStatus::Draft, 200],
    ]);
});
