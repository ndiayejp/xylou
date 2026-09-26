<?php

declare(strict_types=1);

namespace App\Domain\Children\Enums;

// Niveau scolaire (CP à 3e). Le libellé affiché vient de vue-i18n (children.grades.*).
enum Grade: string
{
    case Cp = 'cp';
    case Ce1 = 'ce1';
    case Ce2 = 'ce2';
    case Cm1 = 'cm1';
    case Cm2 = 'cm2';
    case Sixieme = '6e';
    case Cinquieme = '5e';
    case Quatrieme = '4e';
    case Troisieme = '3e';

    public function isPrimary(): bool
    {
        return in_array($this, [self::Cp, self::Ce1, self::Ce2, self::Cm1, self::Cm2], true);
    }
}
