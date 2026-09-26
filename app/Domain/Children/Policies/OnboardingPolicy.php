<?php

declare(strict_types=1);

namespace App\Domain\Children\Policies;

use App\Domain\Children\Models\Onboarding;
use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Models\User;

final class OnboardingPolicy
{
    public function update(User $user, Onboarding $onboarding): bool
    {
        return $user->hasRole(Role::Parent->value) && $onboarding->parent_id === $user->id;
    }
}
