<?php

declare(strict_types=1);

namespace App\Domain\Identity\Events;

use App\Domain\Children\Models\ChildProfile;
use App\Domain\Identity\Models\User;

// Sortie par le code parent.
final readonly class KidSessionClosed
{
    public function __construct(public User $parent, public ChildProfile $child) {}
}
