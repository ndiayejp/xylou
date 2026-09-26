<?php

declare(strict_types=1);

namespace App\Http\Controllers\Parent;

use App\Domain\Children\Models\ChildProfile;
use App\Domain\Identity\Events\KidSessionOpened;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

// Le parent ouvre l'espace de son enfant : sa session se ferme, celle de l'enfant s'ouvre.
// Son identifiant reste en session pour vérifier le code parent à la sortie.
final class KidSessionController extends Controller
{
    public const string PARENT_KEY = 'kid.parent_id';

    public function __invoke(Request $request, ChildProfile $child, #[CurrentUser] User $parent): RedirectResponse
    {
        Gate::authorize('openKidSession', $child);

        Auth::guard('web')->logout();
        $request->session()->regenerate();
        Auth::guard('kid')->login($child);
        $request->session()->put(self::PARENT_KEY, $parent->id);
        event(new KidSessionOpened($parent, $child));

        return to_route('kid.home');
    }
}
