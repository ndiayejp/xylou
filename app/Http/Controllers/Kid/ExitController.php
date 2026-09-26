<?php

declare(strict_types=1);

namespace App\Http\Controllers\Kid;

use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Parent\KidSessionController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

// Retour à l'espace parent : code parent (PIN, sinon mot de passe), 5 essais par minute.
final class ExitController extends Controller
{
    private const int MAX_ATTEMPTS = 5;

    public function show(Request $request): Response
    {
        return Inertia::render('Kid/Exit', [
            'usesPin' => $this->parent($request)?->hasParentPin() ?? false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);
        $parent = $this->parent($request);

        if (! $parent instanceof User) {
            return $this->closeKidSession($request, to_route('login'));
        }

        // Clé liée à l'enfant : stable, et indépendante des cookies de l'appareil.
        $key = 'kid-exit:'.Auth::guard('kid')->id();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'code' => trans('kid.exit.throttle', ['seconds' => RateLimiter::availableIn($key)]),
            ]);
        }

        if (! $parent->checkParentCode($request->string('code')->toString())) {
            RateLimiter::hit($key);

            throw ValidationException::withMessages(['code' => trans('kid.exit.wrong_code')]);
        }

        RateLimiter::clear($key);
        $response = $this->closeKidSession($request, to_route('parent.dashboard'));
        Auth::guard('web')->login($parent);

        return $response;
    }

    private function parent(Request $request): ?User
    {
        $id = $request->session()->get(KidSessionController::PARENT_KEY);

        return is_int($id) ? User::query()->find($id) : null;
    }

    private function closeKidSession(Request $request, RedirectResponse $response): RedirectResponse
    {
        Auth::guard('kid')->logout();
        $request->session()->forget(KidSessionController::PARENT_KEY);
        $request->session()->regenerate();

        return $response;
    }
}
