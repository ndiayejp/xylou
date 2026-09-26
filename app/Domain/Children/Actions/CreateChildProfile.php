<?php

declare(strict_types=1);

namespace App\Domain\Children\Actions;

use App\Domain\Children\Data\ChildProfileInput;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Identity\Models\User;

final class CreateChildProfile
{
    public function __invoke(User $parent, ChildProfileInput $input): ChildProfile
    {
        $child = new ChildProfile($input->attributes());
        $child->owner()->associate($parent);
        $child->save();

        return $child;
    }
}
