<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Children\Models\ChildProfile;
use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Parent\CurrentChildController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Garde « web » explicite : pendant une session enfant, seule la garde « kid » est connectée.
        $user = $request->user('web');

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user === null ? null : [
                    ...$user->only(['id', 'name', 'email', 'email_verified_at']),
                    'roles' => $user->getRoleNames(),
                ],
            ],
            'parent' => fn (): ?array => $user instanceof User && $user->hasRole(Role::Parent->value)
                ? $this->parentSpace($request, $user)
                : null,
            // Session enfant : seulement le prénom, rien de l'espace parent.
            'kid' => function (): ?array {
                $child = Auth::guard('kid')->user();

                return $child instanceof ChildProfile
                    ? ['id' => $child->id, 'firstName' => $child->first_name]
                    : null;
            },
        ];
    }

    /**
     * Enfants du parent pour le sélecteur, et enfant courant (choisi en session, sinon le premier).
     *
     * @return array{children: list<array{id: int, firstName: string, grade: string, birthYear: int|null}>, currentChildId: int|null}
     */
    private function parentSpace(Request $request, User $user): array
    {
        $children = array_values(ChildProfile::query()->ownedBy($user)->get()
            ->map(fn (ChildProfile $child): array => [
                'id' => $child->id,
                'firstName' => $child->first_name,
                'grade' => $child->grade->value,
                'birthYear' => $child->birth_year,
            ])
            ->all());

        $ids = array_column($children, 'id');
        $selected = $request->session()->get(CurrentChildController::SESSION_KEY);

        return [
            'children' => $children,
            'currentChildId' => in_array($selected, $ids, true) ? $selected : ($ids[0] ?? null),
        ];
    }
}
