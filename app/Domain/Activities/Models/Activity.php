<?php

declare(strict_types=1);

namespace App\Domain\Activities\Models;

use App\Domain\Activities\Enums\ActivityDifficulty;
use App\Domain\Activities\Enums\ActivityFormat;
use App\Domain\Activities\Enums\ActivitySource;
use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Exceptions\ActivityRuleViolation;
use App\Domain\Activities\Policies\ActivityPolicy;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Curriculum\Models\Skill;
use App\Domain\Curriculum\Models\Subject;
use App\Domain\Curriculum\Models\Universe;
use App\Domain\Identity\Models\User;
use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;

/**
 * Le statut ne change que par les Actions du domaine (§6.3). Supprimée, l'activité reste 30 jours
 * dans la corbeille, puis « model:prune » l'efface avec ses items.
 *
 * @property string $id ULID
 * @property int $owner_id
 * @property int|null $child_profile_id null : modèle de bibliothèque
 * @property int $subject_id
 * @property int $skill_id
 * @property int|null $universe_id
 * @property string $title
 * @property ActivityFormat $format
 * @property ActivityDifficulty $difficulty
 * @property int $duration_minutes
 * @property ActivityStatus $status
 * @property ActivitySource $source
 * @property string|null $learning_objective
 * @property string|null $context_summary
 * @property int|null $reviewed_by
 * @property Carbon|null $reviewed_at
 * @property Carbon|null $archived_at
 * @property int $version
 * @property Carbon|null $deleted_at
 * @property-read User $owner
 * @property-read ChildProfile|null $childProfile
 * @property-read Subject $subject
 * @property-read Skill $skill
 * @property-read Universe|null $universe
 * @property-read Collection<int, ActivityItem> $items
 */
#[UseFactory(ActivityFactory::class)]
#[UsePolicy(ActivityPolicy::class)]
class Activity extends Model
{
    /** @use HasFactory<ActivityFactory> */
    use HasFactory, HasUlids, Prunable, Searchable, SoftDeletes;

    public const array DURATIONS = [5, 10, 15, 20];

    public const int TRASH_DAYS = 30;

    protected $fillable = [
        'title', 'format', 'difficulty', 'duration_minutes', 'status', 'source',
        'learning_objective', 'context_summary', 'archived_at',
    ];

    protected $attributes = ['version' => 1];

    protected function casts(): array
    {
        return [
            'format' => ActivityFormat::class,
            'difficulty' => ActivityDifficulty::class,
            'duration_minutes' => 'integer',
            'status' => ActivityStatus::class,
            'source' => ActivitySource::class,
            'reviewed_at' => 'datetime',
            'archived_at' => 'datetime',
            'version' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return BelongsTo<ChildProfile, $this> */
    public function childProfile(): BelongsTo
    {
        return $this->belongsTo(ChildProfile::class);
    }

    /** @return BelongsTo<Subject, $this> */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /** @return BelongsTo<Skill, $this> */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** @return HasMany<ActivityItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(ActivityItem::class)->orderBy('position');
    }

    /**
     * Index de recherche (Meilisearch, auto-hébergé) : de quoi retrouver une activité par ses mots,
     * et l'auteur pour filtrer. Rien sur l'enfant.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing(['skill:id,label', 'subject:id,key', 'universe:id,key']);

        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'title' => $this->title,
            'skill' => $this->skill->label,
            'objective' => $this->learning_objective,
            'subject' => $this->subject->key,
            'universe' => $this->universe?->key,
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with(['skill:id,label', 'subject:id,key', 'universe:id,key']);
    }

    // Prête pour la relecture ou l'enfant : au moins une question, toutes complètes.
    public function ensureReady(): void
    {
        $items = $this->items()->get();

        if ($items->isEmpty()) {
            throw ActivityRuleViolation::withoutItems();
        }
        if ($items->contains(fn (ActivityItem $item): bool => ! $item->isComplete())) {
            throw ActivityRuleViolation::incompleteItems();
        }
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    // Encore restaurable : dans la corbeille depuis moins de 30 jours.
    public function isRestorable(): bool
    {
        return $this->deleted_at instanceof Carbon
            && $this->deleted_at->greaterThan(now()->subDays(self::TRASH_DAYS));
    }

    /** @return Builder<self> */
    public function prunable(): Builder
    {
        return static::onlyTrashed()->where('deleted_at', '<=', now()->subDays(self::TRASH_DAYS));
    }

    /** @param Builder<self> $query */
    #[Scope]
    protected function ownedBy(Builder $query, User $user): void
    {
        $query->where('owner_id', $user->id);
    }
}
