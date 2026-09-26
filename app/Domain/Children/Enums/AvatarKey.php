<?php

declare(strict_types=1);

namespace App\Domain\Children\Enums;

// Avatars de l'enfant (maquette, écran 2) : un pictogramme, jamais une photo (§11.1).
enum AvatarKey: string
{
    case Rocket = 'rocket';
    case Ball = 'ball';
    case Paw = 'paw';
    case Brick = 'brick';
    case Music = 'music';
    case Star = 'star';
}
