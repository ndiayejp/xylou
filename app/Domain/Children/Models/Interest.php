<?php

declare(strict_types=1);

namespace App\Domain\Children\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Passion du référentiel (données de référence, créées par migration). Libellé : vue-i18n « interests.<key> ».
 *
 * @property int $id
 * @property string $key
 * @property string $category
 * @property string $icon_key
 * @property int $position
 */
class Interest extends Model
{
    public $timestamps = false;
}
