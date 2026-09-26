<?php

declare(strict_types=1);

namespace App\Domain\Children\Policies;

use App\Domain\Children\Models\ChildProfile;
use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Models\User;

// Seul le parent propriétaire voit et gère le profil. L'accès des pros passera par le partage (étape 9).
final class ChildProfilePolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole(Role::Parent->value);
    }

    public function view(User $user, ChildProfile $child): bool
    {
        return $this->owns($user, $child);
    }

    public function update(User $user, ChildProfile $child): bool
    {
        return $this->owns($user, $child);
    }

    public function delete(User $user, ChildProfile $child): bool
    {
        return $this->owns($user, $child);
    }

    // Ouvrir l'espace de l'enfant sur l'appareil du parent (§7.2).
    public function openKidSession(User $user, ChildProfile $child): bool
    {
        return $this->owns($user, $child);
    }

    private function owns(User $user, ChildProfile $child): bool
    {
        return $user->hasRole(Role::Parent->value) && $child->isOwnedBy($user);
    }
}
