<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Domain\Identity\Actions\SetParentPin;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ParentPinController extends Controller
{
    public function update(Request $request, #[CurrentUser] User $user, SetParentPin $setParentPin): RedirectResponse
    {
        $request->validate(['pin' => ['required', 'string', 'regex:/^\d{4,6}$/', 'confirmed']]);

        $setParentPin($user, $request->string('pin')->toString());

        return back()->with('status', 'parent-pin-updated');
    }

    public function destroy(#[CurrentUser] User $user, SetParentPin $setParentPin): RedirectResponse
    {
        $setParentPin($user, null);

        return back()->with('status', 'parent-pin-removed');
    }
}
