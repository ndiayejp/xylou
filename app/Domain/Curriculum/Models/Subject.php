<?php

declare(strict_types=1);

namespace App\Domain\Curriculum\Models;

use Database\Factories\SubjectFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Matière du référentiel (fichier database/data/curriculum/<clé>.yaml). Libellé : vue-i18n « subjects.<key> ».
 *
 * @property int $id
 * @property string $key
 * @property string $color_token jeton de couleur « subject.<token> » (tokens.ts)
 * @property string $icon_key
 * @property int $position
 */
#[UseFactory(SubjectFactory::class)]
class Subject extends Model
{
    /** @use HasFactory<SubjectFactory> */
    use HasFactory;

    protected $fillable = ['key', 'color_token', 'icon_key', 'position'];

    /** @return HasMany<Skill, $this> */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class)->orderBy('position');
    }
}
