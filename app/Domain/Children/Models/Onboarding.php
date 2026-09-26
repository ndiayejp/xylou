<?php

declare(strict_types=1);

namespace App\Domain\Children\Models;

use App\Domain\Children\Policies\OnboardingPolicy;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Description d'un enfant, écran par écran (2 à 7). L'écran 1 (compte) précède la création.
 *
 * @property int $id
 * @property int $parent_id
 * @property int|null $child_profile_id
 * @property int $reached_step
 * @property Carbon|null $completed_at
 * @property-read User $parent parent_id est obligatoire (clé étrangère, suppression en cascade)
 * @property-read ChildProfile|null $child
 */
#[UsePolicy(OnboardingPolicy::class)]
class Onboarding extends Model
{
    public const int FIRST_STEP = 2;

    public const int LAST_STEP = 7;

    protected $fillable = ['reached_step'];

    protected function casts(): array
    {
        return [
            'reached_step' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /** @return BelongsTo<ChildProfile, $this> */
    public function child(): BelongsTo
    {
        return $this->belongsTo(ChildProfile::class, 'child_profile_id');
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    // On peut revenir sur une étape déjà atteinte, jamais sauter en avant ; les étapes 3+ exigent l'enfant.
    public function allows(int $step): bool
    {
        return $step >= self::FIRST_STEP
            && $step <= min($this->reached_step, self::LAST_STEP)
            && ($step === self::FIRST_STEP || $this->child_profile_id !== null);
    }

    /** @param Builder<self> $query */
    #[Scope]
    protected function inProgressFor(Builder $query, User $parent): void
    {
        $query->where('parent_id', $parent->id)->whereNull('completed_at')->latest('id');
    }
}
