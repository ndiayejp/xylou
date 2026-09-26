<?php

declare(strict_types=1);

namespace App\Domain\Children\Enums;

// Objectifs de l'écran 4 (maquette). « Other » s'accompagne d'une note libre.
enum Goal: string
{
    case RegainConfidence = 'regain_confidence';
    case ProgressMaths = 'progress_maths';
    case ImproveReading = 'improve_reading';
    case Spelling = 'spelling';
    case Comprehension = 'comprehension';
    case Autonomy = 'autonomy';
    case PrepareAssessment = 'prepare_assessment';
    case Other = 'other';
}
