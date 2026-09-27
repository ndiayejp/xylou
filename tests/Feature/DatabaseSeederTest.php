<?php

declare(strict_types=1);

use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Models\Activity;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Identity\Models\User;

// Les tests E2E s'appuient sur ces données de démonstration.
test('le seeder crée les comptes de démo et la bibliothèque du parent', function (): void {
    $this->seed();

    $parent = User::query()->where('email', 'parent@example.com')->firstOrFail();

    expect(User::query()->where('email', 'pro@example.com')->exists())->toBeTrue()
        ->and(ChildProfile::query()->ownedBy($parent)->pluck('first_name')->all())->toBe(['Emma', 'Lucas'])
        ->and(Activity::query()->ownedBy($parent)->count())->toBe(6)
        ->and(Activity::query()->where('status', ActivityStatus::Archived)->count())->toBe(1);
});
