<?php

declare(strict_types=1);

use App\Domain\Curriculum\Enums\Grade;

test('neuf niveaux, du CP à la 3e', function (): void {
    expect(array_column(Grade::cases(), 'value'))
        ->toBe(['cp', 'ce1', 'ce2', 'cm1', 'cm2', '6e', '5e', '4e', '3e']);
});

test('le primaire va du CP au CM2', function (): void {
    expect(array_map(fn (Grade $grade): bool => $grade->isPrimary(), Grade::cases()))
        ->toBe([true, true, true, true, true, false, false, false, false]);
});

test('rang et cycle des programmes officiels', function (): void {
    expect(array_map(fn (Grade $grade): int => $grade->rank(), Grade::cases()))
        ->toBe([1, 2, 3, 4, 5, 6, 7, 8, 9])
        ->and(array_map(fn (Grade $grade): int => $grade->cycle(), Grade::cases()))
        ->toBe([2, 2, 2, 3, 3, 3, 4, 4, 4]);
});

test('une plage de niveaux, bornes comprises', function (): void {
    expect(Grade::range(Grade::Ce2, Grade::Sixieme))->toBe([Grade::Ce2, Grade::Cm1, Grade::Cm2, Grade::Sixieme])
        ->and(Grade::range(Grade::Cm1, Grade::Ce1))->toBe([]);
});
