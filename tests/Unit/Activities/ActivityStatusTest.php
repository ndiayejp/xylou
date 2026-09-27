<?php

declare(strict_types=1);

use App\Domain\Activities\Enums\ActivityStatus as S;

// Machine à états du §6.3, plus draft → approved (activité écrite par l'adulte).
const ALLOWED = [
    'generating' => ['pending_review', 'generation_failed'],
    'generation_failed' => ['generating'],
    'pending_review' => ['approved', 'draft', 'generating'],
    'draft' => ['pending_review', 'approved'],
    'approved' => ['archived'],
    'archived' => ['approved'],
];

test('chaque transition est autorisée ou refusée selon la machine à états', function (S $from, S $to): void {
    expect($from->canTransitionTo($to))->toBe(in_array($to->value, ALLOWED[$from->value], true));
})->with(fn (): array => collect(S::cases())
    ->crossJoin(S::cases())
    ->mapWithKeys(fn (array $pair): array => ["{$pair[0]->value} → {$pair[1]->value}" => $pair])
    ->all());

test('seules les activités générées ou en cours de génération n’ont pas de contenu', function (): void {
    expect(collect(S::cases())->reject->hasContent()->values()->all())
        ->toBe([S::Generating, S::GenerationFailed]);
});
