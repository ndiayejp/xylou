<?php

declare(strict_types=1);

namespace App\Domain\Children\Actions;

use App\Domain\Children\Models\ChildProfile;
use App\Domain\Children\Models\LearningPreferences;
use InvalidArgumentException;

// Écran 6 : façons d'apprendre, durée idéale d'une séance, rappel doux.
final class SetLearningPreferences
{
    /** @param  list<string>  $styles */
    public function __invoke(ChildProfile $child, array $styles, int $sessionMinutes, bool $gentleReminder): LearningPreferences
    {
        if (! in_array($sessionMinutes, LearningPreferences::SESSION_MINUTES, true)) {
            throw new InvalidArgumentException("Durée de séance inconnue : {$sessionMinutes} min.");
        }

        return $child->learningPreferences()->updateOrCreate([], [
            ...array_map(fn (string $style): bool => in_array($style, $styles, true), array_combine(LearningPreferences::STYLES, LearningPreferences::STYLES)),
            'session_minutes' => $sessionMinutes,
            'gentle_reminder' => $gentleReminder,
        ]);
    }
}
