<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Activities\Enums\ActivityDifficulty;
use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Models\Activity;
use App\Domain\Activities\Queries\LibraryQuery;
use App\Domain\Curriculum\Enums\Grade;
use App\Domain\Curriculum\Models\Skill;
use App\Domain\Curriculum\Models\Subject;
use App\Domain\Curriculum\Models\Universe;
use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Models\User;
use App\Http\Navigation\CurrentChild;
use App\Http\Requests\LibraryRequest;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

// Bibliothèque d'activités, commune aux espaces parent et pro (routes parent.library et pro.library).
final class LibraryController extends Controller
{
    private const int PER_PAGE = 24;

    public function __invoke(LibraryRequest $request, #[CurrentUser] User $user, LibraryQuery $library): Response
    {
        Gate::authorize('create', Activity::class);

        // Espace parent : le niveau part de la classe de l'enfant courant.
        $defaultGrade = $user->hasRole(Role::Parent->value) ? CurrentChild::of($request, $user)?->grade : null;
        $filters = $request->filters($defaultGrade);
        $page = $library($user, $filters)->paginate(self::PER_PAGE)->withQueryString();

        return Inertia::render('Library/Index', [
            'activities' => [
                'data' => array_values($page->getCollection()->map($this->card(...))->all()),
                'total' => $page->total(),
                'prev' => $page->previousPageUrl(),
                'next' => $page->nextPageUrl(),
            ],
            'total' => Activity::query()->ownedBy($user)->count(),
            'filters' => LibraryRequest::values($filters),
            'options' => [
                'subjects' => Subject::query()->orderBy('position')->pluck('key'),
                'skills' => $library->skills($user, $filters->subject)->get(['id', 'label'])
                    ->map(fn (Skill $skill): array => ['id' => $skill->id, 'label' => $skill->label]),
                'grades' => array_map(fn (Grade $g): string => $g->value, Grade::cases()),
                'durations' => Activity::DURATIONS,
                'difficulties' => array_map(fn (ActivityDifficulty $d): string => $d->value, ActivityDifficulty::cases()),
                'universes' => Universe::query()->orderBy('position')->pluck('key'),
                'statuses' => array_map(fn (ActivityStatus $s): string => $s->value, ActivityStatus::cases()),
            ],
            'space' => $user->hasRole(Role::Professional->value) ? 'pro' : 'parent',
        ]);
    }

    /** @return array<string, mixed> */
    private function card(Activity $activity): array
    {
        return [
            'id' => $activity->id,
            'title' => $activity->title,
            'subject' => $activity->subject->key,
            'skill' => $activity->skill->label,
            'gradeMin' => $activity->skill->grade_min?->value,
            'gradeMax' => $activity->skill->grade_max?->value,
            'universe' => $activity->universe?->key,
            'durationMinutes' => $activity->duration_minutes,
            'status' => $activity->status->value,
            'source' => $activity->source->value,
            'purgeAt' => $activity->deleted_at?->copy()->addDays(Activity::TRASH_DAYS)->toDateString(),
        ];
    }
}
