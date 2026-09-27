<?php

declare(strict_types=1);

namespace App\Domain\Activities\Actions;

use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Models\Activity;
use App\Domain\Identity\Models\User;

final readonly class ArchiveActivity
{
    public function __construct(private ChangeActivityStatus $changeStatus) {}

    public function __invoke(Activity $activity, User $actor): Activity
    {
        return ($this->changeStatus)($activity, ActivityStatus::Archived, $actor);
    }
}
