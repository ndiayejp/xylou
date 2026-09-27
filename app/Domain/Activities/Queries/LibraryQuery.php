<?php

declare(strict_types=1);

namespace App\Domain\Activities\Queries;

use App\Domain\Activities\Data\LibraryFilters;
use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Models\Activity;
use App\Domain\Curriculum\Models\Skill;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;

// Activités de l'adulte, filtrées ; les plus récentes d'abord. Recherche plein texte : PR 4 (Meilisearch).
final class LibraryQuery
{
    /** @return Builder<Activity> */
    public function __invoke(User $user, LibraryFilters $filters): Builder
    {
        $query = $filters->trash
            ? Activity::onlyTrashed()->ownedBy($user)
            : Activity::query()->ownedBy($user);

        if (! $filters->trash) {
            $filters->status instanceof ActivityStatus
                ? $query->where('status', $filters->status)
                : $query->where('status', '!=', ActivityStatus::Archived);
        }

        return $query
            ->when($filters->subject, fn (Builder $q, string $key) => $q->whereRelation('subject', 'key', $key))
            ->when($filters->skillId, fn (Builder $q, int $id) => $q->where('skill_id', $id))
            ->when($filters->grade, fn (Builder $q, $grade) => $q->whereHas(
                'skill',
                fn (Builder $skill) => $skill->forGrade($grade),
            ))
            ->when($filters->durationMinutes, fn (Builder $q, int $minutes) => $q->where('duration_minutes', $minutes))
            ->when($filters->difficulty, fn (Builder $q, $difficulty) => $q->where('difficulty', $difficulty))
            ->when($filters->universe, fn (Builder $q, string $key) => $q->whereRelation('universe', 'key', $key))
            ->with(['subject:id,key', 'skill:id,label,grade_min,grade_max', 'universe:id,key'])
            ->latest($filters->trash ? 'deleted_at' : 'updated_at')
            ->orderByDesc('id');
    }

    // Compétences présentes dans les activités de l'adulte (options du filtre « Compétence »).
    /** @return Builder<Skill> */
    public function skills(User $user, ?string $subject): Builder
    {
        return Skill::query()
            ->whereIn('id', Activity::query()->ownedBy($user)->select('skill_id'))
            ->when($subject, fn (Builder $q, string $key) => $q->whereRelation('subject', 'key', $key))
            ->orderBy('label');
    }
}
