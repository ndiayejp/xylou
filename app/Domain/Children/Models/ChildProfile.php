<?php

declare(strict_types=1);

namespace App\Domain\Children\Models;

use App\Domain\Children\Enums\AvatarKey;
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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property int $owner_id
 * @property string $first_name
 * @property int|null $birth_year
 * @property Grade $grade
 * @property string $language
 * @property AvatarKey|null $avatar_key
 * @property array<string, bool>|null $comfort_settings
 * @property string|null $difficulty_observation
 */
#[UseFactory(ChildProfileFactory::class)]
#[UsePolicy(ChildProfilePolicy::class)]
class ChildProfile extends Model implements AuthenticatableContract
{
    // Garde « kid » : l'enfant n'a ni mot de passe ni jeton « se souvenir de moi ».
    use Authenticatable;

    /** @use HasFactory<ChildProfileFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    public function getRememberTokenName(): string
    {
        return '';
    }

    // Journal « children » : création, modification, suppression, sans aucune valeur de champ (§11.4).
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('children');
    }

    protected $fillable = ['first_name', 'birth_year', 'grade', 'language', 'avatar_key', 'comfort_settings'];

    protected function casts(): array
    {
        return [
            'birth_year' => 'integer',
            'grade' => Grade::class,
            'avatar_key' => AvatarKey::class,
            'comfort_settings' => 'array',
            // Observation du parent (écran 5) : jamais montrée à l'enfant ni envoyée à l'IA.
            'difficulty_observation' => 'encrypted',
        ];
    }

    // Options de confort (lecture à voix haute, police lisible, sans chronomètre), désactivées par défaut.
    public function comfort(string $option): bool
    {
        return (bool) ($this->comfort_settings[$option] ?? false);
    }

    /** @return BelongsToMany<Interest, $this> */
    public function interests(): BelongsToMany
    {
        return $this->belongsToMany(Interest::class, 'child_interest')->withPivot('rank')->orderByPivot('rank');
    }

    /** @return HasMany<CustomInterest, $this> */
    public function customInterests(): HasMany
    {
        return $this->hasMany(CustomInterest::class)->orderBy('id');
    }

    /** @return HasMany<ChildDifficulty, $this> */
    public function difficulties(): HasMany
    {
        return $this->hasMany(ChildDifficulty::class)->orderBy('id');
    }

    /** @return HasOne<LearningPreferences, $this> */
    public function learningPreferences(): HasOne
    {
        return $this->hasOne(LearningPreferences::class);
    }

    /** @return HasMany<ChildGoal, $this> */
    public function goals(): HasMany
    {
        return $this->hasMany(ChildGoal::class)->orderByDesc('is_primary')->orderBy('id');
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
