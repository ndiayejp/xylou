<?php

declare(strict_types=1);

namespace App\Domain\Curriculum\Enums;

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

    // Rang dans la scolarité : CP = 1, 3e = 9.
    public function rank(): int
    {
        return (int) array_search($this, self::cases(), true) + 1;
    }

    // Cycle des programmes officiels : 2 (CP à CE2), 3 (CM1 à 6e), 4 (5e à 3e).
    public function cycle(): int
    {
        return match (true) {
            $this->rank() <= 3 => 2,
            $this->rank() <= 6 => 3,
            default => 4,
        };
    }

    /** @return list<self> du niveau $from au niveau $to inclus */
    public static function range(self $from, self $to): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $grade): bool => $grade->rank() >= $from->rank() && $grade->rank() <= $to->rank(),
        ));
    }
}
