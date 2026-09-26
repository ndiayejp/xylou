<?php

declare(strict_types=1);

namespace App\Domain\Children\Data;

use App\Domain\Children\Enums\AvatarKey;
use App\Domain\Children\Enums\Grade;

// Saisie de l'écran 2. spatie/laravel-data n'est pas encore installé : simple objet immuable.
final readonly class ChildProfileInput
{
    public function __construct(
        public string $firstName,
        public Grade $grade,
        public ?int $birthYear = null,
        public string $language = 'fr',
        public ?AvatarKey $avatar = null,
        public bool $readAloud = false,
        public bool $dyslexiaFont = false,
        public bool $noTimer = false,
    ) {}

    /** @return array<string, mixed> */
    public function attributes(): array
    {
        return [
            'first_name' => $this->firstName,
            'grade' => $this->grade,
            'birth_year' => $this->birthYear,
            'language' => $this->language,
            'avatar_key' => $this->avatar,
            'comfort_settings' => [
                'read_aloud' => $this->readAloud,
                'dyslexia_font' => $this->dyslexiaFont,
                'no_timer' => $this->noTimer,
            ],
        ];
    }
}
