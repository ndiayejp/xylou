<?php

declare(strict_types=1);

namespace App\Domain\Identity\Enums;

// Rôles des comptes, stockés par spatie/laravel-permission (ADR 0011). L'enfant n'a pas de compte.
enum Role: string
{
    case Parent = 'parent';
    case Professional = 'professional';
    case Admin = 'admin';
}
