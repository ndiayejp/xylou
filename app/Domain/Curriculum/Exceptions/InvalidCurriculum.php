<?php

declare(strict_types=1);

namespace App\Domain\Curriculum\Exceptions;

use RuntimeException;

// Fichier du référentiel mal formé : le message dit quel fichier et quoi corriger.
final class InvalidCurriculum extends RuntimeException
{
    public static function in(string $file, string $reason): self
    {
        return new self(basename($file).' : '.$reason);
    }
}
