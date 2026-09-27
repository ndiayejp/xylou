<?php

declare(strict_types=1);

namespace App\Domain\Activities\Queries;

use App\Domain\Activities\Data\LibraryFilters;
use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Models\Activity;
use App\Domain\Curriculum\Models\Skill;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;

// Activités de l'adulte, filtrées ; les plus récentes d'abord, ou les plus pertinentes avec un texte.
final class LibraryQuery
{
    // Au-delà, la recherche est trop vague pour être utile : on garde les plus pertinents.
    private const int MAX_HITS = 500;

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

        if ($filters->text !== '') {
            $this->search($query, $user, $filters);
        }

        return $query
            ->when($filters->subject, fn (Builder $q, string $key) => $q->whereRelation('subject', 'key', $key))
            ->when($filters->skillId, fn (Builder $q, int $id) => $q->where('skill_id', $id))
            ->when($filters->grade, fn (Builder $q, $grade) => $q->whereHas(
                'skill',
                fn (Builder $skill) => $skill->forGrade($grade),
            ))
            ->when($filters->duration, fn (Builder $q, string $duration) => match ($duration) {
                InterpretLibrarySearch::SHORT => $q->where('duration_minutes', '<=', 10),
                InterpretLibrarySearch::LONG => $q->where('duration_minutes', '>=', 15),
                default => $q->where('duration_minutes', (int) $duration),
            })
            ->when($filters->difficulty, fn (Builder $q, $difficulty) => $q->where('difficulty', $difficulty))
            ->when($filters->universe, fn (Builder $q, string $key) => $q->whereRelation('universe', 'key', $key))
            ->with(['subject:id,key', 'skill:id,label,grade_min,grade_max', 'universe:id,key'])
            ->latest($filters->trash ? 'deleted_at' : 'updated_at')
            ->orderByDesc('id');
    }

    // Plein texte par Scout (Meilisearch), puis les filtres en base ; l'ordre suit la pertinence.
    /** @param Builder<Activity> $query */
    private function search(Builder $query, User $user, LibraryFilters $filters): void
    {
        $search = Activity::search($filters->text)->where('owner_id', $user->id)->take(self::MAX_HITS);
        if ($filters->trash) {
            $search->onlyTrashed();
        }

        /** @var list<string> $ids */
        $ids = $search->keys()->all();
        $query->whereIn('activities.id', $ids);

        if ($ids !== []) {
            $cases = implode(' ', array_map(fn (int $rank): string => "WHEN ? THEN {$rank}", array_keys($ids)));
            $query->orderByRaw("CASE activities.id {$cases} END", $ids);
        }
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
