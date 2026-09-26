<?php

declare(strict_types=1);

namespace App\Domain\Identity\Events;

use App\Domain\Children\Models\ChildProfile;
use App\Domain\Identity\Models\User;

// Code parent refusé à la sortie de la session enfant ; $locked : ce refus a déclenché le blocage.
final readonly class ParentCodeRejected
{
    public function __construct(public User $parent, public ChildProfile $child, public bool $locked) {}
}
