<?php

declare(strict_types=1);

namespace App\Domain\Curriculum\Models;

use App\Domain\Curriculum\Enums\Grade;
use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Compétence du référentiel. Un domaine (ex. « Nombres et calculs ») est une compétence sans parent
 * ni niveau ; les compétences travaillées en activité sont ses enfants, avec une plage de niveaux.
 * Retirée du fichier YAML, elle est marquée « retired_at » (des activités peuvent y faire référence).
 *
 * @property int $id
 * @property int $subject_id
 * @property int|null $parent_skill_id
 * @property string $code ex. MATH.CE2.PROB.2STEPS
 * @property string $label
 * @property string|null $description
 * @property Grade|null $grade_min
 * @property Grade|null $grade_max
 * @property int $position
 * @property Carbon|null $retired_at
 * @property-read Subject $subject
 * @property-read Skill|null $parent
 */
#[UseFactory(SkillFactory::class)]
class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory;

    protected $fillable = [
        'subject_id', 'parent_skill_id', 'code', 'label', 'description',
        'grade_min', 'grade_max', 'position', 'retired_at',
    ];

    protected function casts(): array
    {
        return [
            'grade_min' => Grade::class,
            'grade_max' => Grade::class,
            'retired_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Subject, $this> */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /** @return BelongsTo<Skill, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_skill_id');
    }

    /** @return HasMany<Skill, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_skill_id')->orderBy('position');
    }

    public function isDomain(): bool
    {
        return $this->parent_skill_id === null;
    }

    /** @return list<Grade> */
    public function grades(): array
    {
        return $this->grade_min instanceof Grade && $this->grade_max instanceof Grade
            ? Grade::range($this->grade_min, $this->grade_max)
            : [];
    }

    // Compétences en vigueur travaillées en activité (hors domaines).
    /** @param Builder<self> $query */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereNull('retired_at')->whereNotNull('parent_skill_id');
    }

    /** @param Builder<self> $query */
    #[Scope]
    protected function forGrade(Builder $query, Grade $grade): void
    {
        $query->whereIn('grade_min', array_map(
            fn (Grade $g): string => $g->value,
            Grade::range(Grade::Cp, $grade),
        ))->whereIn('grade_max', array_map(
            fn (Grade $g): string => $g->value,
            Grade::range($grade, Grade::Troisieme),
        ));
    }
}
