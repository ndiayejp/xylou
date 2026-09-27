<?php

declare(strict_types=1);

namespace App\Domain\Activities\Data;

use App\Domain\Activities\Enums\ActivityDifficulty;
use App\Domain\Curriculum\Enums\Grade;

// Ce que la recherche en langage naturel a compris : des filtres, et le texte restant.
final readonly class SearchIntent
{
    public function __construct(
        public ?string $subject = null,
        public ?Grade $grade = null,
        public ?string $child = null,
        public ?string $duration = null,
        public ?ActivityDifficulty $difficulty = null,
        public ?string $universe = null,
        public string $text = '',
    ) {}

    /** @return array<string, string> filtres compris, dans l'ordre d'affichage */
    public function understood(): array
    {
        return array_filter([
            'subject' => $this->subject,
            'grade' => $this->grade?->value,
            'child' => $this->child,
            'duration' => $this->duration,
            'difficulty' => $this->difficulty?->value,
            'universe' => $this->universe,
        ], fn (?string $value): bool => $value !== null);
    }
}
