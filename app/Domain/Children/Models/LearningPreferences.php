<?php

declare(strict_types=1);

namespace App\Domain\Children\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Façons d'apprendre, durée de séance et rappel doux (écran 6). Une ligne par enfant.
 *
 * @property int $child_profile_id
 * @property int $session_minutes
 * @property bool $gentle_reminder
 */
class LearningPreferences extends Model
{
    public const array STYLES = ['visual', 'concrete_examples', 'small_steps', 'repetition', 'short_explanations', 'interactive'];

    public const array SESSION_MINUTES = [5, 10, 15, 20];

    protected $table = 'learning_preferences';

    protected $primaryKey = 'child_profile_id';

    public $incrementing = false;

    protected $fillable = [...self::STYLES, 'session_minutes', 'gentle_reminder'];

    protected function casts(): array
    {
        return [
            ...array_fill_keys(self::STYLES, 'boolean'),
            'session_minutes' => 'integer',
            'gentle_reminder' => 'boolean',
        ];
    }

    /** @return list<string> */
    public function styles(): array
    {
        return array_values(array_filter(self::STYLES, fn (string $style): bool => (bool) $this->getAttribute($style)));
    }
}
