<?php

declare(strict_types=1);

namespace App\Domain\Children\Actions;

use App\Domain\Children\Models\ChildProfile;
use App\Domain\Children\Support\DifficultyCatalog;
use Illuminate\Support\Facades\DB;

// Écran 5 : remplace les difficultés observées et l'observation libre (chiffrée). Tout est facultatif.
final class RecordDifficulties
{
    /** @param  list<string>  $ids  identifiants « matière.clé » du catalogue */
    public function __invoke(ChildProfile $child, array $ids, ?string $observation): void
    {
        $known = DifficultyCatalog::ids();

        DB::transaction(function () use ($child, $ids, $observation, $known): void {
            $child->difficulties()->delete();

            foreach (array_unique($ids) as $id) {
                if (in_array($id, $known, true)) {
                    [$subject, $key] = explode('.', $id, 2);
                    $child->difficulties()->create(['subject' => $subject, 'key' => $key]);
                }
            }

            $observation = $observation === null ? null : trim($observation);
            $child->forceFill(['difficulty_observation' => $observation === '' ? null : $observation])->save();
        });
    }
}
