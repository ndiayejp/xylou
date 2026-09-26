<?php

declare(strict_types=1);

namespace App\Domain\Curriculum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Univers d'illustration d'une activité (space, football, forest), créé par migration.
 * Libellé : vue-i18n « universes.<key> » ; décor : XUniverseScene.
 *
 * @property int $id
 * @property string $key
 * @property string $illustration_set
 * @property int $position
 */
class Universe extends Model
{
    public $timestamps = false;

    /** @return HasMany<Interest, $this> */
    public function interests(): HasMany
    {
        return $this->hasMany(Interest::class);
    }
}
