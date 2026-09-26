<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Models\User;

// null retire le PIN : le mot de passe redevient le code parent.
final class SetParentPin
{
    public function __invoke(User $parent, ?string $pin): void
    {
        $parent->forceFill(['parent_pin' => $pin])->save();
    }
}
