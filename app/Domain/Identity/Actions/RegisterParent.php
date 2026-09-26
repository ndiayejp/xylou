<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;

// L'inscription publique ne crée que des comptes parent ; les pros arrivent par invitation (étape 9).
final class RegisterParent
{
    public function __invoke(string $name, string $email, string $password): User
    {
        $user = DB::transaction(function () use ($name, $email, $password): User {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);
            $user->assignRole(Role::Parent->value);

            return $user;
        });

        event(new Registered($user));

        return $user;
    }
}
