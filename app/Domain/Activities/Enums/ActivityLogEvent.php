<?php

declare(strict_types=1);

namespace App\Domain\Activities\Enums;

// Événements du journal « activities » : identifiants et statuts, jamais le contenu de l'activité.
enum ActivityLogEvent: string
{
    case Created = 'created';
    case Duplicated = 'duplicated';
    case Updated = 'updated';
    case StatusChanged = 'status_changed';
    case Deleted = 'deleted';
    case Restored = 'restored';

    public const string LOG_NAME = 'activities';
}
