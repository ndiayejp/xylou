<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Domain\Children\Actions\StartOnboarding;
use App\Domain\Children\Models\Onboarding;
use App\Domain\Identity\Actions\RegisterParent;
use App\Domain\Identity\Models\User;
use App\Domain\Privacy\Actions\RecordConsent;
use App\Domain\Privacy\Enums\ConsentKind;
use App\Http\Controllers\Controller;
use App\Http\Navigation\OnboardingRoute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

// Écran 1 « Votre compte » : inscription du parent et consentements, puis description de l'enfant.
// Le mail de vérification part tout de suite ; il n'est exigé qu'à l'entrée dans l'espace parent.
final class AccountController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Onboarding/Account');
    }

    public function store(
        Request $request,
        RegisterParent $registerParent,
        RecordConsent $recordConsent,
        StartOnboarding $startOnboarding,
    ): RedirectResponse {
        $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Password::defaults()],
            'parental_authority' => ['accepted'],
            'terms' => ['accepted'],
        ]);

        $onboarding = DB::transaction(function () use ($request, $registerParent, $recordConsent, $startOnboarding): Onboarding {
            $user = $registerParent(
                trim($request->string('first_name')->toString().' '.$request->string('last_name')->toString()),
                $request->string('email')->toString(),
                $request->string('password')->toString(),
            );

            $version = (string) config('xylou.consent_version');
            foreach (ConsentKind::cases() as $kind) {
                $recordConsent($user, $kind, $version, $request->ip());
            }

            return $startOnboarding($user);
        });

        Auth::login($onboarding->parent);
        $request->session()->regenerate();
        $request->session()->passwordConfirmed();

        return redirect(OnboardingRoute::resume($onboarding));
    }
}
