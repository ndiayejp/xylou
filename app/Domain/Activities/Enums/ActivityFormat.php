<?php

declare(strict_types=1);

namespace App\Domain\Activities\Enums;

// §15.3. Libellés : vue-i18n « activities.formats.<value> ».
enum ActivityFormat: string
{
    case Exercise = 'exercise';
    case Quiz = 'quiz';
    case Problem = 'problem';
    case InteractiveStory = 'interactive_story';
    case Flashcards = 'flashcards';
    case Challenge = 'challenge';
}
