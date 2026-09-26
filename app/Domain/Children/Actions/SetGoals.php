<?php

declare(strict_types=1);

namespace App\Domain\Children\Actions;

use App\Domain\Children\Enums\Goal;
use App\Domain\Children\Models\ChildProfile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

// Remplace les objectifs : un seul principal, choisi parmi les objectifs retenus ; note pour « Autre ».
final class SetGoals
{
    /** @param  list<Goal>  $goals */
    public function __invoke(ChildProfile $child, array $goals, Goal $primary, ?string $note = null): void
    {
        if (! in_array($primary, $goals, true)) {
            throw new InvalidArgumentException('L’objectif principal doit faire partie des objectifs retenus.');
        }

        DB::transaction(function () use ($child, $goals, $primary, $note): void {
            $child->goals()->delete();

            foreach (array_unique($goals, SORT_REGULAR) as $goal) {
                $child->goals()->create([
                    'goal' => $goal,
                    'is_primary' => $goal === $primary,
                    'note' => $goal === Goal::Other ? $note : null,
                ]);
            }
        });
    }
}
