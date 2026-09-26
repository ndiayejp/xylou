<?php

declare(strict_types=1);

namespace App\Domain\Children\Actions;

use App\Domain\Children\Models\ChildProfile;
use App\Domain\Children\Models\Interest;
use Illuminate\Support\Facades\DB;

// Remplace les passions de l'enfant : celles du référentiel (dans l'ordre choisi) et les passions libres.
final class UpdateInterests
{
    /**
     * @param  list<string>  $keys  clés du référentiel
     * @param  list<string>  $custom  passions libres
     */
    public function __invoke(ChildProfile $child, array $keys, array $custom): void
    {
        DB::transaction(function () use ($child, $keys, $custom): void {
            $ids = Interest::query()->whereIn('key', $keys)->pluck('id', 'key');
            $ranked = [];
            foreach (array_values(array_unique($keys)) as $rank => $key) {
                if ($ids->has($key)) {
                    $ranked[$ids[$key]] = ['rank' => $rank + 1];
                }
            }
            $child->interests()->sync($ranked);

            $child->customInterests()->delete();
            foreach (array_unique(array_map(trim(...), $custom)) as $label) {
                if ($label !== '') {
                    $child->customInterests()->create(['label' => $label]);
                }
            }
        });
    }
}
