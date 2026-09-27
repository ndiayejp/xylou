<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities;

use App\Domain\Activities\Actions\DuplicateActivity;
use App\Domain\Activities\Models\Activity;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class DuplicateController extends Controller
{
    public function __invoke(Activity $activity, #[CurrentUser] User $user, DuplicateActivity $duplicate): RedirectResponse
    {
        Gate::authorize('duplicate', $activity);

        $duplicate($activity, $user);

        return back();
    }
}
