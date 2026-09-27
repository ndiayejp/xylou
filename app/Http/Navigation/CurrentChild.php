<?php

declare(strict_types=1);

namespace App\Http\Navigation;

use App\Domain\Children\Models\ChildProfile;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Parent\CurrentChildController;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

// Enfant courant de l'espace parent : celui choisi dans le sélecteur (session), sinon le premier.
final class CurrentChild
{
    /** @param Collection<int, ChildProfile>|null $children enfants du parent, s'ils sont déjà chargés */
    public static function of(Request $request, User $parent, ?Collection $children = null): ?ChildProfile
    {
        $children ??= ChildProfile::query()->ownedBy($parent)->get();
        $selected = $request->session()->get(CurrentChildController::SESSION_KEY);

        return $children->firstWhere('id', $selected) ?? $children->first();
    }
}
