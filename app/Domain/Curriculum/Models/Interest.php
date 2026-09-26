<?php

declare(strict_types=1);

namespace App\Domain\Curriculum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Passion du référentiel (données de référence, créées par migration). Libellé : vue-i18n « interests.<key> ».
 *
 * @property int $id
 * @property string $key
 * @property string $category
 * @property string $icon_key
 * @property int $position
 * @property int|null $universe_id univers qui l'illustre, s'il existe
 * @property-read Universe|null $universe
 */
class Interest extends Model
{
    public $timestamps = false;

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }
}
