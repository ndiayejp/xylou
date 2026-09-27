<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities;

use App\Domain\Activities\Actions\RestoreActivity;
use App\Domain\Activities\Models\Activity;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

// Sortie de la corbeille (route liée avec withTrashed).
final class RestoreController extends Controller
{
    public function __invoke(Activity $activity, #[CurrentUser] User $user, RestoreActivity $restore): RedirectResponse
    {
        Gate::authorize('restore', $activity);

        $restore($activity, $user);

        return back();
    }
}
