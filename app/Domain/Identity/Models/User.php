<?php

declare(strict_types=1);

namespace App\Domain\Identity\Models;

use App\Domain\Identity\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

#[UseFactory(UserFactory::class)]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'parent_pin',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'parent_pin' => 'hashed',
        ];
    }

    public function hasParentPin(): bool
    {
        return $this->parent_pin !== null;
    }

    // Code parent pour sortir de la session enfant (§7.2) : le PIN s'il existe, sinon le mot de passe.
    public function checkParentCode(string $code): bool
    {
        return Hash::check($code, $this->parent_pin ?? $this->password);
    }

    // La 2FA est obligatoire pour les professionnels (§7.1), facultative pour les parents.
    public function requiresTwoFactor(): bool
    {
        return $this->hasRole(Role::Professional->value);
    }

    // Espace d'accueil après connexion ; null pour un compte sans espace (admin, en attendant le back-office).
    public function homeRouteName(): ?string
    {
        return match (true) {
            $this->hasRole(Role::Parent->value) => 'parent.dashboard',
            $this->hasRole(Role::Professional->value) => 'pro.dashboard',
            default => null,
        };
    }
}
