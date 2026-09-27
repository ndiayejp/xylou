<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities;

use App\Domain\Activities\Actions\ArchiveActivity;
use App\Domain\Activities\Actions\UnarchiveActivity;
use App\Domain\Activities\Models\Activity;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

// Archive d'une activité : store = archiver, destroy = sortir des archives.
final class ArchiveController extends Controller
{
    public function store(Activity $activity, #[CurrentUser] User $user, ArchiveActivity $archive): RedirectResponse
    {
        Gate::authorize('archive', $activity);

        $archive($activity, $user);

        return back();
    }

    public function destroy(Activity $activity, #[CurrentUser] User $user, UnarchiveActivity $unarchive): RedirectResponse
    {
        Gate::authorize('archive', $activity);

        $unarchive($activity, $user);

        return back();
    }
}
