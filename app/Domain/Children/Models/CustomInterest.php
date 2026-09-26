<?php

declare(strict_types=1);

namespace App\Domain\Children\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Passion libre saisie par le parent (« son club de foot », « les robots »).
 *
 * @property int $id
 * @property int $child_profile_id
 * @property string $label
 */
class CustomInterest extends Model
{
    protected $fillable = ['label'];
}
