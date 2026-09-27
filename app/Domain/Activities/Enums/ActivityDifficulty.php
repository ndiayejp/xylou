<?php

declare(strict_types=1);

namespace App\Domain\Activities\Enums;

enum ActivityDifficulty: string
{
    case Discovery = 'discovery';
    case Practice = 'practice';
    case Consolidation = 'consolidation';
    case Challenge = 'challenge';
}
