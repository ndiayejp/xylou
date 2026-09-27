<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Activities\Enums\ActivityDifficulty;
use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Models\Activity;
use App\Domain\Activities\Queries\InterpretLibrarySearch;
use App\Domain\Activities\Queries\LibraryQuery;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Curriculum\Enums\Grade;
use App\Domain\Curriculum\Models\Skill;
use App\Domain\Curriculum\Models\Subject;
use App\Domain\Curriculum\Models\Universe;
use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Models\User;
use App\Http\Navigation\CurrentChild;
use App\Http\Requests\LibraryRequest;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

// Bibliothèque d'activités, commune aux espaces parent et pro (routes parent.library et pro.library).
final class LibraryController extends Controller
{
    private const int PER_PAGE = 24;

    private const string UNDERSTOOD = 'library.understood';

    public function __invoke(LibraryRequest $request, #[CurrentUser] User $user, LibraryQuery $library, InterpretLibrarySearch $interpret): Response|RedirectResponse
    {
        Gate::authorize('create', Activity::class);

        if ($request->filled('ask')) {
            return $this->interpret($request, $user, $interpret);
        }

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
            'understood' => $request->session()->get(self::UNDERSTOOD),
        ]);
    }

    // Phrase en langage naturel : ses mots-clés remplissent les filtres (qui restent modifiables un par un),
    // le reste devient le texte cherché. On redirige vers l'adresse filtrée.
    private function interpret(LibraryRequest $request, User $user, InterpretLibrarySearch $interpret): RedirectResponse
    {
        $children = ChildProfile::query()->ownedBy($user)->get()
            ->mapWithKeys(fn (ChildProfile $child): array => [$child->first_name => $child->grade])
            ->all();
        $intent = $interpret($request->string('ask')->limit(200, '')->value(), $children);
        $understood = $intent->understood();

        // « grade= » (niveau effacé) arrive en null : on le renvoie vide pour ne pas retomber sur celui de l'enfant.
        $current = array_map(fn (mixed $value): string => is_scalar($value) ? (string) $value : '', $request->only(['subject', 'skill', 'grade', 'duration', 'difficulty', 'universe', 'status']));
        $query = array_filter([
            ...$current,
            ...array_diff_key($understood, ['child' => true]),
            'q' => $intent->text,
        ], fn (mixed $value, string $key): bool => $key === 'grade' || filled($value), ARRAY_FILTER_USE_BOTH);
        // Une compétence choisie avant n'a plus de sens dans une autre matière.
        if (isset($understood['subject'])) {
            unset($query['skill']);
        }

        return to_route($request->route()?->getName() ?? 'parent.library', $query)
            ->with(self::UNDERSTOOD, $understood === [] ? null : [...$understood, 'text' => $intent->text]);
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
