<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities;

use App\Domain\Activities\Actions\DeleteActivity;
use App\Domain\Activities\Models\Activity;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class ActivityController extends Controller
{
    // Mise à la corbeille ; la page propose « Annuler » (RestoreController).
    public function destroy(Activity $activity, #[CurrentUser] User $user, DeleteActivity $delete): RedirectResponse
    {
        Gate::authorize('delete', $activity);

        $delete($activity, $user);

        return back();
    }
}
