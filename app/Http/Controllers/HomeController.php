<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Identity\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;

// Route « dashboard » de Breeze : aiguille chaque compte vers son espace.
final class HomeController extends Controller
{
    public function __invoke(#[CurrentUser] User $user): RedirectResponse
    {
        $route = $user->homeRouteName();
        abort_if($route === null, 403);

        return to_route($route);
    }
}
