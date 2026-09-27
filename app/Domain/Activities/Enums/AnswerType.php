<?php

declare(strict_types=1);

namespace App\Domain\Activities\Enums;

// §15.3. La forme de « expected_answer » dépend du type ; elle sera précisée avec l'éditeur et le moteur d'exercice.
enum AnswerType: string
{
    case Number = 'number';
    case Text = 'text';
    case SingleChoice = 'single_choice';
    case MultipleChoice = 'multiple_choice';
    case Ordering = 'ordering';
    case Matching = 'matching';
    case FillBlank = 'fill_blank';
}
