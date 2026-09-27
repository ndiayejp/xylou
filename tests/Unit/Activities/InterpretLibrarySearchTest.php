<?php

declare(strict_types=1);

use App\Domain\Activities\Data\SearchIntent;
use App\Domain\Activities\Enums\ActivityDifficulty;
use App\Domain\Activities\Queries\InterpretLibrarySearch;
use App\Domain\Curriculum\Enums\Grade;

function interpret(string $sentence, array $children = ['Emma' => Grade::Ce2, 'Lucas' => Grade::Sixieme]): SearchIntent
{
    return (new InterpretLibrarySearch)($sentence, $children);
}

test('l’exemple de la maquette', function (): void {
    $intent = interpret('problèmes courts avec des animaux pour Emma');

    expect($intent->understood())->toBe([
        'subject' => 'maths',
        'grade' => 'ce2',
        'child' => 'Emma',
        'duration' => 'short',
        'universe' => 'forest',
    ])->and($intent->text)->toBe('');
});

test('accents, casse et ponctuation n’y changent rien', function (): void {
    expect(interpret('FUSÉE, Mathématiques !')->understood())
        ->toBe(['subject' => 'maths', 'universe' => 'space']);
});

test('une classe écrite l’emporte sur celle de l’enfant nommé', function (): void {
    expect(interpret('pour Lucas en CM2')->understood())
        ->toBe(['grade' => 'cm2', 'child' => 'Lucas']);
});

test('les classes s’écrivent de plusieurs façons', function (string $word, Grade $grade): void {
    expect(interpret($word)->grade)->toBe($grade);
})->with([
    ['CP', Grade::Cp],
    ['ce1', Grade::Ce1],
    ['6e', Grade::Sixieme],
    ['6ème', Grade::Sixieme],
    ['sixième', Grade::Sixieme],
    ['3e', Grade::Troisieme],
]);

test('une durée chiffrée est exacte, si elle existe', function (): void {
    expect(interpret('défi de 15 minutes')->understood())->toBe(['duration' => '15', 'difficulty' => 'challenge'])
        ->and(interpret('12 min')->duration)->toBeNull()
        ->and(interpret('longue lecture')->duration)->toBe('long');
});

test('les difficultés', function (string $word, ActivityDifficulty $difficulty): void {
    expect(interpret($word)->difficulty)->toBe($difficulty);
})->with([
    ['facile', ActivityDifficulty::Discovery],
    ['entraînement', ActivityDifficulty::Practice],
    ['révisions', ActivityDifficulty::Consolidation],
    ['difficiles', ActivityDifficulty::Challenge],
]);

test('le premier mot compris l’emporte, les autres ne changent rien', function (): void {
    expect(interpret('français maths foot espace')->understood())
        ->toBe(['subject' => 'french', 'universe' => 'football']);
});

test('le reste devient le texte cherché, sans les mots vides', function (): void {
    $intent = interpret('les fractions et la conjugaison pour Emma');

    expect($intent->text)->toBe('fractions conjugaison')
        ->and($intent->understood())->toBe(['grade' => 'ce2', 'child' => 'Emma']);
});

test('le prénom d’un enfant qui n’est pas le sien reste du texte', function (): void {
    expect(interpret('Adam', ['Emma' => Grade::Ce2])->text)->toBe('Adam');
});

test('une phrase vide ne comprend rien', function (): void {
    expect(interpret('   ')->understood())->toBe([])
        ->and(interpret('   ')->text)->toBe('');
});
