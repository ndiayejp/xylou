<?php

declare(strict_types=1);

namespace App\Domain\Activities\Enums;

enum ActivitySource: string
{
    case Ai = 'ai';
    case Manual = 'manual';
    case Duplicate = 'duplicate';
    case ProReco = 'pro_reco';
}
