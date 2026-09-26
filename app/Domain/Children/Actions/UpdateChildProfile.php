<?php

declare(strict_types=1);

namespace App\Domain\Children\Actions;

use App\Domain\Children\Data\ChildProfileInput;
use App\Domain\Children\Models\ChildProfile;

final class UpdateChildProfile
{
    public function __invoke(ChildProfile $child, ChildProfileInput $input): ChildProfile
    {
        $child->update($input->attributes());

        return $child;
    }
}
