<?php

declare(strict_types=1);

namespace App\Domain\Activities\Exceptions;

use App\Domain\Activities\Enums\ActivityStatus;
use DomainException;

final class ActivityRuleViolation extends DomainException
{
    public static function transition(ActivityStatus $from, ActivityStatus $to): self
    {
        return new self("Transition interdite : {$from->value} → {$to->value}.");
    }

    public static function withoutItems(): self
    {
        return new self('Une activité sans question ne peut pas être validée.');
    }

    public static function notDuplicable(ActivityStatus $status): self
    {
        return new self("Une activité « {$status->value} » n'a pas de contenu à dupliquer.");
    }

    public static function notRestorable(): self
    {
        return new self('Cette activité n’est plus dans la corbeille.');
    }

    public static function inactiveSkill(): self
    {
        return new self('La compétence doit être en vigueur et travaillée en activité.');
    }

    public static function invalidDuration(int $minutes): self
    {
        return new self("Durée non prévue : {$minutes} minutes.");
    }

    public static function foreignChild(): self
    {
        return new self('L’enfant n’appartient pas à l’auteur de l’activité.');
    }
}
