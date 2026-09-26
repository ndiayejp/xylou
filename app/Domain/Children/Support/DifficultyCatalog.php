<?php

declare(strict_types=1);

namespace App\Domain\Children\Support;

// Difficultés proposées à l'écran 5, par matière, et « au quotidien ». Libellés : vue-i18n
// « difficulties.<matière>.<clé> ». Le rattachement aux compétences viendra avec le référentiel (étape 4).
final class DifficultyCatalog
{
    public const string DAILY = 'daily';

    /** @var array<string, list<string>> */
    public const array ITEMS = [
        'maths' => [
            'problem_solving', 'multi_step_problems', 'fractions', 'mental_math',
            'reading_statements', 'geometry', 'times_tables', 'measures',
        ],
        'french' => [
            'reading_fluency', 'reading_comprehension', 'spelling', 'grammar',
            'conjugation', 'writing', 'vocabulary',
        ],
        'sciences' => ['scientific_method', 'reading_documents', 'science_vocabulary', 'reasoning'],
        'other' => ['organisation', 'memorisation', 'oral_expression', 'homework_routine'],
        self::DAILY => ['discouraged_long', 'hesitates_help', 'tired_evening', 'concentration'],
    ];

    // Identifiant « matière.clé » de chaque difficulté possible.
    /** @return list<string> */
    public static function ids(): array
    {
        $ids = [];
        foreach (self::ITEMS as $subject => $keys) {
            foreach ($keys as $key) {
                $ids[] = $subject.'.'.$key;
            }
        }

        return $ids;
    }
}
