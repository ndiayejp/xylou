<?php

declare(strict_types=1);

namespace App\Domain\Activities\Data;

use App\Domain\Activities\Enums\ActivityDifficulty;
use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Curriculum\Enums\Grade;

// Filtres de la bibliothèque. Sans statut : tout sauf les archives ; « trash » : la corbeille seule.
// Durée : « 5 », « 10 »… (exacte), « short » (≤ 10 min) ou « long » (≥ 15 min). « text » : plein texte (Scout).
final readonly class LibraryFilters
{
    public function __construct(
        public ?string $subject = null,
        public ?int $skillId = null,
        public ?Grade $grade = null,
        public ?string $duration = null,
        public ?ActivityDifficulty $difficulty = null,
        public ?string $universe = null,
        public ?ActivityStatus $status = null,
        public bool $trash = false,
        public string $text = '',
    ) {}
}
