<?php

declare(strict_types=1);

namespace App\Domain\Activities\Queries;

use App\Domain\Activities\Data\SearchIntent;
use App\Domain\Activities\Enums\ActivityDifficulty;
use App\Domain\Activities\Models\Activity;
use App\Domain\Curriculum\Enums\Grade;
use Illuminate\Support\Str;

/**
 * Recherche en langage naturel simple (§13, étape 4) : des mots-clés deviennent des filtres,
 * le reste part en recherche plein texte. Déterministe, sans IA : « problèmes courts avec des
 * animaux pour Emma » → Maths, CE2 (Emma), ≤ 10 min, Forêt.
 */
final class InterpretLibrarySearch
{
    public const string SHORT = 'short';

    public const string LONG = 'long';

    /** @var array<string, string> mot (sans accent, minuscule) => matière */
    private const array SUBJECTS = [
        'math' => 'maths', 'maths' => 'maths', 'mathematique' => 'maths', 'mathematiques' => 'maths',
        'calcul' => 'maths', 'calculs' => 'maths', 'probleme' => 'maths', 'problemes' => 'maths',
        'francais' => 'french',
    ];

    /** @var array<string, string> */
    private const array UNIVERSES = [
        'espace' => 'space', 'fusee' => 'space', 'fusees' => 'space', 'planete' => 'space',
        'planetes' => 'space', 'etoile' => 'space', 'etoiles' => 'space', 'astronaute' => 'space',
        'astronautes' => 'space',
        'foot' => 'football', 'football' => 'football', 'ballon' => 'football', 'ballons' => 'football',
        'match' => 'football',
        'animal' => 'forest', 'animaux' => 'forest', 'foret' => 'forest', 'forets' => 'forest',
        'nature' => 'forest', 'arbre' => 'forest', 'arbres' => 'forest',
    ];

    /** @var array<string, string> */
    private const array DIFFICULTIES = [
        'facile' => 'discovery', 'faciles' => 'discovery', 'decouverte' => 'discovery',
        'entrainement' => 'practice',
        'consolidation' => 'consolidation', 'revision' => 'consolidation', 'revisions' => 'consolidation',
        'difficile' => 'challenge', 'difficiles' => 'challenge', 'defi' => 'challenge', 'defis' => 'challenge',
    ];

    /** @var array<string, string> */
    private const array DURATIONS = [
        'court' => self::SHORT, 'courts' => self::SHORT, 'courte' => self::SHORT, 'courtes' => self::SHORT,
        'rapide' => self::SHORT, 'rapides' => self::SHORT,
        'long' => self::LONG, 'longs' => self::LONG, 'longue' => self::LONG, 'longues' => self::LONG,
    ];

    /** @var array<string, string> */
    private const array GRADES = ['6eme' => '6e', 'sixieme' => '6e', '5eme' => '5e', '4eme' => '4e', '3eme' => '3e'];

    private const array STOP_WORDS = [
        'a', 'au', 'aux', 'avec', 'd', 'de', 'des', 'du', 'en', 'et', 'l', 'la', 'le', 'les', 'ma', 'mes',
        'mon', 'ou', 'par', 'pour', 'qui', 'sur', 'un', 'une', 'activite', 'activites', 'exercice', 'exercices',
        'min', 'mn', 'minute', 'minutes',
    ];

    /** @param array<string, Grade> $children prénom => classe (enfants du parent) */
    public function __invoke(string $sentence, array $children = []): SearchIntent
    {
        $childGrades = [];
        foreach ($children as $name => $grade) {
            $childGrades[$this->fold($name)] = [$name, $grade];
        }

        $found = [];
        $rest = [];
        $words = preg_split('/[^\p{L}\p{N}]+/u', $sentence, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($words as $index => $word) {
            $key = $this->fold($word);
            $next = isset($words[$index + 1]) ? $this->fold($words[$index + 1]) : null;

            match (true) {
                isset(self::SUBJECTS[$key]) => $found['subject'] ??= self::SUBJECTS[$key],
                isset(self::UNIVERSES[$key]) => $found['universe'] ??= self::UNIVERSES[$key],
                isset(self::DIFFICULTIES[$key]) => $found['difficulty'] ??= self::DIFFICULTIES[$key],
                isset(self::DURATIONS[$key]) => $found['duration'] ??= self::DURATIONS[$key],
                ctype_digit($key) && in_array((int) $key, Activity::DURATIONS, true)
                    && in_array($next, ['min', 'mn', 'minute', 'minutes'], true) => $found['duration'] ??= $key,
                ($grade = Grade::tryFrom(self::GRADES[$key] ?? $key)) instanceof Grade => $found['grade'] ??= $grade,
                isset($childGrades[$key]) => $found['child'] ??= $childGrades[$key],
                in_array($key, self::STOP_WORDS, true) || mb_strlen($key) < 2 => null,
                default => $rest[] = $word,
            };
        }

        // Une classe écrite l'emporte sur celle de l'enfant nommé.
        $child = $found['child'] ?? null;

        return new SearchIntent(
            subject: $found['subject'] ?? null,
            grade: $found['grade'] ?? $child[1] ?? null,
            child: $child[0] ?? null,
            duration: $found['duration'] ?? null,
            difficulty: isset($found['difficulty']) ? ActivityDifficulty::from($found['difficulty']) : null,
            universe: $found['universe'] ?? null,
            text: implode(' ', $rest),
        );
    }

    private function fold(string $word): string
    {
        return Str::lower(Str::ascii($word));
    }
}
