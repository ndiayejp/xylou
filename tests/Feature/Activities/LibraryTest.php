<?php

declare(strict_types=1);

use App\Domain\Activities\Enums\ActivityDifficulty;
use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Models\Activity;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Curriculum\Enums\Grade;
use App\Domain\Curriculum\Models\Skill;
use App\Domain\Curriculum\Models\Subject;
use App\Domain\Curriculum\Models\Universe;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia;
use Inertia\Testing\AssertableInertia as Assert;

/** @return list<string> ids des activités affichées */
function libraryIds(User $user, array $query = [], string $space = 'parent'): array
{
    $ids = [];
    test()->actingAs($user)->get(route("{$space}.library", $query))
        ->assertOk()
        ->assertInertia(function (Assert $page) use (&$ids): void {
            $page->component('Library/Index');
            $ids = array_column($page->toArray()['props']['activities']['data'], 'id');
        });
    sort($ids);

    return $ids;
}

function sortedIds(Activity ...$activities): array
{
    $ids = array_map(fn (Activity $a): string => $a->id, $activities);
    sort($ids);

    return $ids;
}

describe('accès', function (): void {
    test('un invité est renvoyé vers la connexion', function (): void {
        $this->get(route('parent.library'))->assertRedirect(route('login'));
    });

    test('chaque espace a sa bibliothèque', function (): void {
        $this->actingAs(User::factory()->parent()->create())->get(route('pro.library'))->assertForbidden();
        $this->actingAs(User::factory()->professional()->withTwoFactor()->create())->get(route('parent.library'))->assertForbidden();
    });

    test('un pro sans double authentification est renvoyé vers la sécurité', function (): void {
        $this->actingAs(User::factory()->professional()->create())
            ->get(route('pro.library'))
            ->assertRedirect(route('settings.security'));
    });

    test('un pro retrouve ses activités, sans niveau pré-rempli', function (): void {
        $pro = User::factory()->professional()->withTwoFactor()->create();
        $activity = Activity::factory()->for($pro, 'owner')->create();

        expect(libraryIds($pro, space: 'pro'))->toBe([$activity->id]);
        $this->get(route('pro.library'))->assertInertia(fn (Assert $page): AssertableInertia => $page
            ->where('space', 'pro')
            ->where('filters.grade', ''));
    });
});

describe('contenu', function (): void {
    test('seules les activités de l’adulte, sans les archives ni la corbeille', function (): void {
        $parent = User::factory()->parent()->create();
        $draft = Activity::factory()->for($parent, 'owner')->status(ActivityStatus::Draft)->create();
        $approved = Activity::factory()->for($parent, 'owner')->create();
        Activity::factory()->for($parent, 'owner')->status(ActivityStatus::Archived)->create();
        Activity::factory()->for($parent, 'owner')->create()->delete();
        Activity::factory()->create();

        expect(libraryIds($parent))->toBe(sortedIds($draft, $approved));
        $this->get(route('parent.library'))->assertInertia(fn (Assert $page): AssertableInertia => $page
            ->where('total', 3)
            ->where('activities.total', 2));
    });

    test('une carte donne ce que la page affiche', function (): void {
        $parent = User::factory()->parent()->create();
        $skill = Skill::factory()->create(['label' => 'Problèmes en 2 étapes', 'grade_min' => Grade::Ce1, 'grade_max' => Grade::Ce2]);
        $activity = Activity::factory()->for($parent, 'owner')->for($skill)->create([
            'subject_id' => $skill->subject_id,
            'universe_id' => Universe::query()->where('key', 'space')->value('id'),
            'title' => 'Mission Mars',
        ]);

        $this->actingAs($parent)->get(route('parent.library'))->assertInertia(fn (Assert $page): AssertableInertia => $page
            ->where('activities.data.0', [
                'id' => $activity->id,
                'title' => 'Mission Mars',
                'subject' => $skill->subject->key,
                'skill' => 'Problèmes en 2 étapes',
                'gradeMin' => 'ce1',
                'gradeMax' => 'ce2',
                'universe' => 'space',
                'durationMinutes' => 10,
                'status' => 'approved',
                'source' => 'manual',
                'purgeAt' => null,
            ]));
    });

    test('la corbeille liste les activités supprimées et leur date d’effacement', function (): void {
        $parent = User::factory()->parent()->create();
        $activity = Activity::factory()->for($parent, 'owner')->create();
        Activity::factory()->for($parent, 'owner')->create();
        $this->freezeTime();
        $activity->delete();

        expect(libraryIds($parent, ['status' => 'deleted']))->toBe([$activity->id]);
        $this->get(route('parent.library', ['status' => 'deleted']))->assertInertia(fn (Assert $page): AssertableInertia => $page
            ->where('activities.data.0.purgeAt', now()->addDays(30)->toDateString())
            ->where('filters.status', 'deleted'));
    });

    test('les résultats sont paginés par 24', function (): void {
        $parent = User::factory()->parent()->create();
        $skill = Skill::factory()->create();
        Activity::factory()->count(25)->for($parent, 'owner')->for($skill)->create(['subject_id' => $skill->subject_id]);

        $this->actingAs($parent)->get(route('parent.library'))->assertInertia(fn (Assert $page): AssertableInertia => $page
            ->has('activities.data', 24)
            ->where('activities.prev', null)
            ->where('activities.next', fn (?string $url): bool => str_contains((string) $url, 'page=2')));
    });
});

describe('filtres', function (): void {
    test('chaque filtre restreint la liste', function (string $filter, Closure $matching, Closure $value): void {
        $parent = User::factory()->parent()->create();
        $kept = Activity::factory()->for($parent, 'owner')->create($matching());
        Activity::factory()->for($parent, 'owner')->status(ActivityStatus::Draft)->create();

        expect(libraryIds($parent, [$filter => $value($kept), 'grade' => '']))->toBe([$kept->id]);
    })->with([
        'matière' => ['subject', fn (): array => [], fn (Activity $a): string => $a->subject->key],
        'compétence' => ['skill', fn (): array => [], fn (Activity $a): int => $a->skill_id],
        'durée' => ['duration', fn (): array => ['duration_minutes' => 20], fn (): int => 20],
        'difficulté' => ['difficulty', fn (): array => ['difficulty' => ActivityDifficulty::Challenge], fn (): string => 'challenge'],
        'thème' => ['universe', fn (): array => ['universe_id' => Universe::query()->where('key', 'forest')->value('id')], fn (): string => 'forest'],
        'statut' => ['status', fn (): array => [], fn (): string => 'approved'],
    ]);

    test('le statut « archivée » montre les archives', function (): void {
        $parent = User::factory()->parent()->create();
        $archived = Activity::factory()->for($parent, 'owner')->status(ActivityStatus::Archived)->create();
        Activity::factory()->for($parent, 'owner')->create();

        expect(libraryIds($parent, ['status' => 'archived']))->toBe([$archived->id]);
    });

    test('le niveau retient les compétences dont la plage contient la classe', function (): void {
        $parent = User::factory()->parent()->create();
        $ce1ToCe2 = Activity::factory()->for($parent, 'owner')
            ->for(Skill::factory()->state(['grade_min' => Grade::Ce1, 'grade_max' => Grade::Ce2]))
            ->create();
        Activity::factory()->for($parent, 'owner')
            ->for(Skill::factory()->state(['grade_min' => Grade::Cm1, 'grade_max' => Grade::Cm2]))
            ->create();

        expect(libraryIds($parent, ['grade' => 'ce2']))->toBe([$ce1ToCe2->id]);
    });

    test('le niveau part de la classe de l’enfant courant, et s’efface', function (): void {
        $parent = User::factory()->parent()->create();
        ChildProfile::factory()->for($parent, 'owner')->create(['grade' => Grade::Cm1, 'first_name' => 'Adam']);
        $cm1 = Activity::factory()->for($parent, 'owner')
            ->for(Skill::factory()->state(['grade_min' => Grade::Cm1, 'grade_max' => Grade::Cm1]))->create();
        $ce2 = Activity::factory()->for($parent, 'owner')->create();

        expect(libraryIds($parent))->toBe([$cm1->id]);
        $this->get(route('parent.library'))->assertInertia(fn (Assert $page): AssertableInertia => $page->where('filters.grade', 'cm1'));

        expect(libraryIds($parent, ['grade' => '']))->toBe(sortedIds($cm1, $ce2));
    });

    test('les compétences proposées sont celles des activités de l’adulte, dans la matière choisie', function (): void {
        $parent = User::factory()->parent()->create();
        $used = Activity::factory()->for($parent, 'owner')->create();
        $other = Activity::factory()->for($parent, 'owner')->create();
        Skill::factory()->create();

        $this->actingAs($parent)->get(route('parent.library'))->assertInertia(fn (Assert $page): AssertableInertia => $page
            ->has('options.skills', 2));
        $this->get(route('parent.library', ['subject' => $used->subject->key]))->assertInertia(fn (Assert $page): AssertableInertia => $page
            ->where('options.skills', [['id' => $used->skill_id, 'label' => $used->skill->label]]));

        expect($other->skill_id)->not->toBe($used->skill_id);
    });

    test('une valeur inconnue est ignorée', function (): void {
        $parent = User::factory()->parent()->create();
        $activity = Activity::factory()->for($parent, 'owner')->create();

        expect(libraryIds($parent, ['status' => 'n-importe', 'duration' => '7', 'difficulty' => 'x', 'skill' => 'abc', 'grade' => 'zz']))
            ->toBe([$activity->id]);
    });

    test('les options couvrent le référentiel', function (): void {
        Subject::factory()->create(['key' => 'maths', 'position' => 1]);

        $this->actingAs(User::factory()->parent()->create())->get(route('parent.library'))
            ->assertInertia(fn (Assert $page): AssertableInertia => $page
                ->where('options.subjects', ['maths'])
                ->where('options.universes', ['space', 'football', 'forest'])
                ->where('options.durations', [5, 10, 15, 20])
                ->has('options.grades', 9)
                ->has('options.statuses', 6));
    });
});

describe('actions', function (): void {
    test('dupliquer, archiver, désarchiver, supprimer puis restaurer', function (): void {
        $parent = User::factory()->parent()->create();
        $activity = Activity::factory()->for($parent, 'owner')->create();
        $this->actingAs($parent)->from(route('parent.library'));

        $this->post(route('activities.duplicate', $activity))->assertRedirect(route('parent.library'));
        expect(Activity::query()->ownedBy($parent)->where('status', ActivityStatus::Draft)->count())->toBe(1);

        $this->post(route('activities.archive', $activity))->assertRedirect(route('parent.library'));
        expect($activity->fresh()?->status)->toBe(ActivityStatus::Archived);

        $this->delete(route('activities.unarchive', $activity))->assertRedirect(route('parent.library'));
        expect($activity->fresh()?->status)->toBe(ActivityStatus::Approved);

        $this->delete(route('activities.destroy', $activity))->assertRedirect(route('parent.library'));
        expect(Activity::query()->find($activity->id))->toBeNull();

        $this->post(route('activities.restore', $activity))->assertRedirect(route('parent.library'));
        expect(Activity::query()->find($activity->id))->not->toBeNull();
    });

    test('un pro agit sur ses activités', function (): void {
        $pro = User::factory()->professional()->withTwoFactor()->create();
        $activity = Activity::factory()->for($pro, 'owner')->create();

        $this->actingAs($pro)->post(route('activities.archive', $activity))->assertRedirect();

        expect($activity->fresh()?->status)->toBe(ActivityStatus::Archived);
    });

    test('personne n’agit sur l’activité d’un autre', function (string $method, string $name): void {
        $activity = Activity::factory()->status(ActivityStatus::Archived)->create();
        if ($name === 'activities.restore') {
            $activity->delete();
        }

        $this->actingAs(User::factory()->parent()->create())
            ->{$method}(route($name, $activity))
            ->assertForbidden();
    })->with([
        ['post', 'activities.duplicate'],
        ['post', 'activities.archive'],
        ['delete', 'activities.unarchive'],
        ['delete', 'activities.destroy'],
        ['post', 'activities.restore'],
    ]);

    test('une action devenue impossible revient avec un message, sans erreur', function (): void {
        $parent = User::factory()->parent()->create();
        $draft = Activity::factory()->for($parent, 'owner')->status(ActivityStatus::Draft)->create();

        $this->actingAs($parent)->from(route('parent.library'))
            ->post(route('activities.archive', $draft))
            ->assertRedirect(route('parent.library'))
            ->assertSessionHasErrors(['activity' => __('activities.refused')]);

        expect($draft->fresh()?->status)->toBe(ActivityStatus::Draft);
    });

    test('une activité dans la corbeille n’accepte que la restauration', function (): void {
        $parent = User::factory()->parent()->create();
        $activity = Activity::factory()->for($parent, 'owner')->create();
        $activity->delete();

        $this->actingAs($parent)->post(route('activities.archive', $activity))->assertNotFound();
    });
});

describe('recherche', function (): void {
    test('le texte cherche dans le titre et la compétence, parmi les activités de l’adulte', function (): void {
        $parent = User::factory()->parent()->create();
        $rocket = Activity::factory()->for($parent, 'owner')->create(['title' => 'La fusée des tables']);
        $bySkill = Activity::factory()->for($parent, 'owner')
            ->for(Skill::factory()->state(['label' => 'Voyage en fusée : les distances']))->create();
        Activity::factory()->for($parent, 'owner')->create(['title' => 'Mission Mars']);
        Activity::factory()->create(['title' => 'La fusée d’un autre parent']);

        expect(libraryIds($parent, ['q' => 'fusée', 'grade' => '']))->toBe(sortedIds($rocket, $bySkill));
        $this->get(route('parent.library', ['q' => 'fusée', 'grade' => '']))
            ->assertInertia(fn (Assert $page): Assert => $page->where('filters.q', 'fusée'));
    });

    test('le texte se combine aux filtres et à la corbeille', function (): void {
        $parent = User::factory()->parent()->create();
        Activity::factory()->for($parent, 'owner')->create(['title' => 'La fusée des tables', 'duration_minutes' => 5]);
        $long = Activity::factory()->for($parent, 'owner')->create(['title' => 'La grande fusée', 'duration_minutes' => 20]);
        $trashed = Activity::factory()->for($parent, 'owner')->create(['title' => 'Fusée oubliée']);
        $trashed->delete();

        expect(libraryIds($parent, ['q' => 'fusée', 'duration' => 'long', 'grade' => '']))->toBe([$long->id])
            ->and(libraryIds($parent, ['q' => 'fusée', 'status' => 'deleted']))->toBe([$trashed->id]);
    });

    test('les durées courtes et longues', function (): void {
        $parent = User::factory()->parent()->create();
        $five = Activity::factory()->for($parent, 'owner')->create(['duration_minutes' => 5]);
        $ten = Activity::factory()->for($parent, 'owner')->create(['duration_minutes' => 10]);
        $fifteen = Activity::factory()->for($parent, 'owner')->create(['duration_minutes' => 15]);

        expect(libraryIds($parent, ['duration' => 'short', 'grade' => '']))->toBe(sortedIds($five, $ten))
            ->and(libraryIds($parent, ['duration' => 'long', 'grade' => '']))->toBe([$fifteen->id]);
    });

    test('une phrase remplit les filtres, garde les autres, et dit ce qu’elle a compris', function (): void {
        $parent = User::factory()->parent()->create();
        ChildProfile::factory()->for($parent, 'owner')->create(['first_name' => 'Emma', 'grade' => Grade::Ce2]);

        $this->actingAs($parent)
            ->get(route('parent.library', ['ask' => 'problèmes courts avec des animaux pour Emma', 'difficulty' => 'practice', 'skill' => '3']))
            ->assertRedirect(route('parent.library', [
                'difficulty' => 'practice',
                'subject' => 'maths',
                'grade' => 'ce2',
                'duration' => 'short',
                'universe' => 'forest',
            ]))
            ->assertSessionHas('library.understood', [
                'subject' => 'maths',
                'grade' => 'ce2',
                'child' => 'Emma',
                'duration' => 'short',
                'universe' => 'forest',
                'text' => '',
            ]);

        $this->get(route('parent.library', ['subject' => 'maths', 'grade' => 'ce2']))
            ->assertInertia(fn (Assert $page): Assert => $page->where('understood.child', 'Emma'));
        $this->get(route('parent.library'))
            ->assertInertia(fn (Assert $page): Assert => $page->where('understood', null));
    });

    test('ce qui n’est pas compris devient le texte cherché', function (): void {
        $this->actingAs(User::factory()->parent()->create())
            ->get(route('parent.library', ['ask' => 'les fractions', 'grade' => '']))
            ->assertRedirect(route('parent.library', ['grade' => '', 'q' => 'fractions']))
            ->assertSessionMissing('library.understood');
    });

    test('un pro cherche dans son espace', function (): void {
        $this->actingAs(User::factory()->professional()->withTwoFactor()->create())
            ->get(route('pro.library', ['ask' => 'défi espace']))
            ->assertRedirect(route('pro.library', ['difficulty' => 'challenge', 'universe' => 'space']));
    });
});

test('une adresse retouchée avec des tableaux ne casse pas la page', function (): void {
    $this->actingAs(User::factory()->parent()->create())
        ->get('/parent/bibliotheque?subject[]=maths&q[]=x&duration[]=5&ask[]=foo')
        ->assertOk();
});
