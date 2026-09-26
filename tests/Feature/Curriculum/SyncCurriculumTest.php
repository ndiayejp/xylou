<?php

declare(strict_types=1);

use App\Domain\Curriculum\Actions\SyncCurriculum;
use App\Domain\Curriculum\Enums\Grade;
use App\Domain\Curriculum\Exceptions\InvalidCurriculum;
use App\Domain\Curriculum\Models\Interest;
use App\Domain\Curriculum\Models\Skill;
use App\Domain\Curriculum\Models\Subject;
use App\Domain\Curriculum\Models\Universe;
use Illuminate\Support\Facades\File;

// Dossier temporaire contenant les fichiers YAML donnés (nom => contenu).
function curriculumDir(array $files): string
{
    $dir = storage_path('framework/testing/curriculum-'.uniqid());
    File::ensureDirectoryExists($dir);
    foreach ($files as $name => $content) {
        File::put($dir.'/'.$name, $content);
    }

    return $dir;
}

function mathsYaml(string $skills = ''): string
{
    return <<<YAML
        subject: { key: maths, color: maths, icon: calculator, position: 1 }
        domains:
          - code: MATH.NUM
            label: Nombres et calculs
            skills:
              - { code: MATH.CP.NUM.COUNT, grades: cp, label: "Dénombrer" }
              - { code: MATH.CE1.NUM.READ, grades: ce1-ce2, label: "Lire, écrire" }
        {$skills}
        YAML;
}

afterEach(fn () => File::deleteDirectory(storage_path('framework/testing')));

describe('le référentiel du dépôt', function (): void {
    beforeEach(fn () => resolve(SyncCurriculum::class)(database_path('data/curriculum')));

    test('maths et français sont chargés, avec leurs domaines', function (): void {
        expect(Subject::query()->orderBy('position')->pluck('key')->all())->toBe(['maths', 'french'])
            ->and(Skill::query()->where('code', 'MATH.CE2.PROB.2STEPS')->sole()->parent?->code)->toBe('MATH.PROB');
    });

    test('chaque classe a de 6 à 10 compétences par matière', function (): void {
        foreach (Subject::all() as $subject) {
            foreach (Grade::cases() as $grade) {
                $count = Skill::query()->active()->forGrade($grade)->where('subject_id', $subject->id)->count();

                expect($count)->toBeGreaterThanOrEqual(6, "{$subject->key} {$grade->value}")
                    ->toBeLessThanOrEqual(10, "{$subject->key} {$grade->value}");
            }
        }
    });

    test('le code d’une compétence commence par sa matière et son premier niveau', function (): void {
        $prefixes = ['maths' => 'MATH', 'french' => 'FR'];

        Skill::query()->active()->with('subject')->each(function (Skill $skill) use ($prefixes): void {
            expect($skill->code)->toStartWith($prefixes[$skill->subject->key].'.'.strtoupper($skill->grade_min->value ?? '').'.');
        });
    });

    test('une deuxième synchronisation ne change rien', function (): void {
        $before = Skill::query()->orderBy('id')->get(['id', 'code', 'label', 'parent_skill_id'])->toArray();

        $report = resolve(SyncCurriculum::class)(database_path('data/curriculum'));

        expect(Skill::query()->orderBy('id')->get(['id', 'code', 'label', 'parent_skill_id'])->toArray())->toBe($before)
            ->and($report['retired'])->toBe(0);
    });
});

test('met à jour un libellé, retire une compétence disparue, la rétablit si elle revient', function (): void {
    $sync = resolve(SyncCurriculum::class);
    $sync(curriculumDir(['maths.yaml' => mathsYaml()]));
    $id = Skill::query()->where('code', 'MATH.CE1.NUM.READ')->value('id');

    $renamed = str_replace('"Dénombrer"', '"Dénombrer une collection"', mathsYaml());
    $withoutRead = preg_replace('/^.*MATH\.CE1\.NUM\.READ.*$/m', '', $renamed) ?? '';
    $report = $sync(curriculumDir(['maths.yaml' => $withoutRead]));

    expect($report['retired'])->toBe(1)
        ->and(Skill::query()->where('code', 'MATH.CP.NUM.COUNT')->value('label'))->toBe('Dénombrer une collection')
        ->and(Skill::query()->find($id)?->retired_at)->not->toBeNull()
        ->and(Skill::query()->active()->pluck('code')->all())->toBe(['MATH.CP.NUM.COUNT']);

    $sync(curriculumDir(['maths.yaml' => mathsYaml()]));

    expect(Skill::query()->find($id)?->retired_at)->toBeNull();
});

test('une plage de niveaux est enregistrée et trouvée pour chaque niveau', function (): void {
    resolve(SyncCurriculum::class)(curriculumDir(['maths.yaml' => mathsYaml()]));

    $skill = Skill::query()->where('code', 'MATH.CE1.NUM.READ')->sole();

    expect($skill->grades())->toBe([Grade::Ce1, Grade::Ce2])
        ->and(Skill::query()->active()->forGrade(Grade::Ce2)->pluck('code')->all())->toBe(['MATH.CE1.NUM.READ'])
        ->and(Skill::query()->active()->forGrade(Grade::Cm1)->count())->toBe(0)
        ->and(Skill::query()->where('code', 'MATH.NUM')->sole()->isDomain())->toBeTrue();
});

test('un fichier invalide est refusé avant toute écriture', function (string $yaml, string $message): void {
    $dir = curriculumDir(['a.yaml' => mathsYaml(), 'b.yaml' => $yaml]);

    expect(fn () => resolve(SyncCurriculum::class)($dir))
        ->toThrow(InvalidCurriculum::class, $message);
    expect(Subject::count())->toBe(0);
})->with([
    'sans matière' => ['domains: []', '« subject » est obligatoire'],
    'sans domaine' => ["subject: { key: fr, color: french, icon: book, position: 2 }\ndomains: []", '« domains » doit être une liste non vide'],
    'niveau inconnu' => [
        "subject: { key: fr, color: french, icon: book, position: 2 }\ndomains:\n  - { code: FR.READ, label: Lecture, skills: [{ code: FR.CP.READ.A, grades: terminale, label: A }] }",
        'FR.CP.READ.A : niveaux invalides',
    ],
    'plage inversée' => [
        "subject: { key: fr, color: french, icon: book, position: 2 }\ndomains:\n  - { code: FR.READ, label: Lecture, skills: [{ code: FR.CP.READ.A, grades: cm1-ce1, label: A }] }",
        'niveaux invalides',
    ],
    'code mal formé' => [
        "subject: { key: fr, color: french, icon: book, position: 2 }\ndomains:\n  - { code: fr-lecture, label: Lecture, skills: [{ code: FR.CP.READ.A, grades: cp, label: A }] }",
        'code invalide « fr-lecture »',
    ],
    'code en double' => [
        "subject: { key: fr, color: french, icon: book, position: 2 }\ndomains:\n  - { code: FR.READ, label: Lecture, skills: [{ code: MATH.CP.NUM.COUNT, grades: cp, label: A }] }",
        'le code MATH.CP.NUM.COUNT existe déjà (a.yaml)',
    ],
    'libellé vide' => [
        "subject: { key: fr, color: french, icon: book, position: 2 }\ndomains:\n  - { code: FR.READ, label: Lecture, skills: [{ code: FR.CP.READ.A, grades: cp, label: '' }] }",
        'FR.CP.READ.A : libellé obligatoire',
    ],
]);

test('la commande charge le référentiel, ou dit ce qui ne va pas', function (): void {
    $this->artisan('curriculum:sync')
        ->expectsOutputToContain('Référentiel chargé : 2 matières')
        ->assertSuccessful();

    $this->artisan('curriculum:sync', ['--path' => curriculumDir(['b.yaml' => 'domains: []'])])
        ->expectsOutputToContain('b.yaml : la clé « subject » est obligatoire.')
        ->assertFailed();
});

test('les univers illustrent certaines passions', function (): void {
    $forest = Universe::query()->where('key', 'forest')->sole();

    expect(Universe::query()->orderBy('position')->pluck('key')->all())->toBe(['space', 'football', 'forest'])
        ->and($forest->interests()->orderBy('key')->pluck('key')->all())->toBe(['animals', 'nature'])
        ->and(Interest::query()->where('key', 'lego')->sole()->universe)->toBeNull();
});

test('les données de démonstration chargent le référentiel', function (): void {
    $this->seed();

    expect(Subject::count())->toBe(2)
        ->and(Skill::query()->active()->count())->toBeGreaterThan(150);
});
