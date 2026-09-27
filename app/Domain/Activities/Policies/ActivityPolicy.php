<?php

declare(strict_types=1);

namespace App\Domain\Activities\Policies;

use App\Domain\Activities\Models\Activity;
use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Models\User;

// Parents et professionnels créent des activités ; seul l'auteur gère les siennes.
final class ActivityPolicy
{
    public function create(User $user): bool
    {
        return $user->hasAnyRole([Role::Parent->value, Role::Professional->value]);
    }

    public function view(User $user, Activity $activity): bool
    {
        return $activity->isOwnedBy($user);
    }

    public function update(User $user, Activity $activity): bool
    {
        return $activity->isOwnedBy($user) && $activity->status->isEditable();
    }

    public function duplicate(User $user, Activity $activity): bool
    {
        return $this->create($user) && $activity->isOwnedBy($user);
    }

    public function archive(User $user, Activity $activity): bool
    {
        return $activity->isOwnedBy($user);
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $activity->isOwnedBy($user);
    }

    public function restore(User $user, Activity $activity): bool
    {
        return $activity->isOwnedBy($user);
    }
}
