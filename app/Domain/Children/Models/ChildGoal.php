<?php

declare(strict_types=1);

namespace App\Domain\Children\Models;

use App\Domain\Children\Enums\Goal;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $child_profile_id
 * @property Goal $goal
 * @property bool $is_primary
 * @property string|null $note
 */
class ChildGoal extends Model
{
    protected $fillable = ['goal', 'is_primary', 'note'];

    protected function casts(): array
    {
        return [
            'goal' => Goal::class,
            'is_primary' => 'boolean',
            'target_date' => 'date',
        ];
    }
}
