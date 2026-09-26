<?php

declare(strict_types=1);

use App\Domain\Children\Enums\Grade;

test('neuf niveaux, du CP à la 3e', function (): void {
    expect(array_column(Grade::cases(), 'value'))
        ->toBe(['cp', 'ce1', 'ce2', 'cm1', 'cm2', '6e', '5e', '4e', '3e']);
});

test('le primaire va du CP au CM2', function (): void {
    expect(array_map(fn (Grade $grade): bool => $grade->isPrimary(), Grade::cases()))
        ->toBe([true, true, true, true, true, false, false, false, false]);
});
