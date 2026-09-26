<?php

declare(strict_types=1);

namespace App\Domain\Children\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Difficulté observée par le parent (privée, jamais montrée à l'enfant).
 *
 * @property int $id
 * @property int $child_profile_id
 * @property string $subject
 * @property string $key
 */
class ChildDifficulty extends Model
{
    protected $fillable = ['subject', 'key'];

    public function id(): string
    {
        return $this->subject.'.'.$this->key;
    }
}
