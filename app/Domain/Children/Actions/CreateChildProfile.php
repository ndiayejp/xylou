<?php

declare(strict_types=1);

namespace App\Domain\Children\Actions;

use App\Domain\Children\Enums\Grade;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Identity\Models\User;

final class CreateChildProfile
{
    public function __invoke(User $parent, string $firstName, Grade $grade, ?int $birthYear = null): ChildProfile
    {
        $child = new ChildProfile([
            'first_name' => $firstName,
            'grade' => $grade,
            'birth_year' => $birthYear,
        ]);
        $child->owner()->associate($parent);
        $child->save();

        return $child;
    }
}
