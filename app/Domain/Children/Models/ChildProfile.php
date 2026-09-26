<?php

declare(strict_types=1);

namespace App\Domain\Children\Models;

use App\Domain\Children\Enums\Grade;
use App\Domain\Children\Policies\ChildProfilePolicy;
use App\Domain\Identity\Models\User;
use Database\Factories\ChildProfileFactory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $owner_id
 * @property string $first_name
 * @property int|null $birth_year
 * @property Grade $grade
 */
#[UseFactory(ChildProfileFactory::class)]
#[UsePolicy(ChildProfilePolicy::class)]
class ChildProfile extends Model implements AuthenticatableContract
{
    // Garde « kid » : l'enfant n'a ni mot de passe ni jeton « se souvenir de moi ».
    use Authenticatable;

    /** @use HasFactory<ChildProfileFactory> */
    use HasFactory, SoftDeletes;

    public function getRememberTokenName(): string
    {
        return '';
    }

    protected $fillable = ['first_name', 'birth_year', 'grade'];

    protected function casts(): array
    {
        return [
            'birth_year' => 'integer',
            'grade' => Grade::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    /** @param Builder<self> $query */
    #[Scope]
    protected function ownedBy(Builder $query, User $user): void
    {
        $query->where('owner_id', $user->id)->orderBy('first_name');
    }
}
