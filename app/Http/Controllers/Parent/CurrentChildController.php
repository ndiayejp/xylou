<?php

declare(strict_types=1);

namespace App\Http\Controllers\Parent;

use App\Domain\Children\Models\ChildProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

// Sélecteur d'enfant de l'espace parent : l'enfant choisi est gardé en session.
final class CurrentChildController extends Controller
{
    public const string SESSION_KEY = 'current_child_id';

    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate(['child_id' => ['required', 'integer']]);
        $child = ChildProfile::query()->findOrFail($request->integer('child_id'));
        Gate::authorize('view', $child);

        $request->session()->put(self::SESSION_KEY, $child->id);

        return back();
    }
}
