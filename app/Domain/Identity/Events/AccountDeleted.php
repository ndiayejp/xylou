<?php

declare(strict_types=1);

namespace App\Domain\Identity\Events;

// Émis avant la suppression : seul l'identifiant est conservé dans le journal.
final readonly class AccountDeleted
{
    public function __construct(public int $userId) {}
}
