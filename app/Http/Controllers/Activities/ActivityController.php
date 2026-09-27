<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities;

use App\Domain\Activities\Actions\CreateActivity;
use App\Domain\Activities\Actions\DeleteActivity;
use App\Domain\Activities\Actions\UpdateActivity;
use App\Domain\Activities\Enums\ActivityDifficulty;
use App\Domain\Activities\Enums\ActivityFormat;
use App\Domain\Activities\Enums\AnswerType;
use App\Domain\Activities\Models\Activity;
use App\Domain\Activities\Models\ActivityItem;
use App\Domain\Curriculum\Enums\Grade;
use App\Domain\Curriculum\Models\Skill;
use App\Domain\Curriculum\Models\Subject;
use App\Domain\Curriculum\Models\Universe;
use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Navigation\CurrentChild;
use App\Http\Requests\ActivityRequest;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

// Éditeur d'activité (création et modification) et mise à la corbeille.
final class ActivityController extends Controller
{
    public function create(Request $request, #[CurrentUser] User $user): Response
    {
        Gate::authorize('create', Activity::class);

        return $this->editor($request, $user, null);
    }

    public function store(ActivityRequest $request, #[CurrentUser] User $user, CreateActivity $create): RedirectResponse
    {
        Gate::authorize('create', Activity::class);

        $create($user, $request->activity(), $request->approving());

        return to_route($this->library($user));
    }

    public function edit(Request $request, Activity $activity, #[CurrentUser] User $user): Response
    {
        Gate::authorize('update', $activity);

        return $this->editor($request, $user, $activity);
    }

    public function update(ActivityRequest $request, Activity $activity, #[CurrentUser] User $user, UpdateActivity $update): RedirectResponse
    {
        Gate::authorize('update', $activity);

        $update($activity, $request->activity(), $user, $request->approving());

        return to_route($this->library($user));
    }

    // Mise à la corbeille ; la page propose « Annuler » (RestoreController).
    public function destroy(Activity $activity, #[CurrentUser] User $user, DeleteActivity $delete): RedirectResponse
    {
        Gate::authorize('delete', $activity);

        $delete($activity, $user);

        return back();
    }

    private function editor(Request $request, User $user, ?Activity $activity): Response
    {
        $parent = $user->hasRole(Role::Parent->value);

        return Inertia::render('Activities/Edit', [
            'activity' => $activity instanceof Activity ? $this->form($activity) : null,
            // Nouvelle activité : le niveau part de la classe de l'enfant courant (espace parent).
            'defaultGrade' => $parent ? CurrentChild::of($request, $user)?->grade->value : null,
            'options' => [
                'subjects' => Subject::query()->orderBy('position')->pluck('key'),
                'skills' => Skill::query()->active()->with('subject:id,key')->orderBy('code')->get()
                    ->map(fn (Skill $skill): array => [
                        'id' => $skill->id,
                        'label' => $skill->label,
                        'subject' => $skill->subject->key,
                        'grades' => array_map(fn (Grade $g): string => $g->value, $skill->grades()),
                    ]),
                'grades' => array_map(fn (Grade $g): string => $g->value, Grade::cases()),
                'universes' => Universe::query()->orderBy('position')->pluck('key'),
                'formats' => array_map(fn (ActivityFormat $f): string => $f->value, ActivityFormat::cases()),
                'difficulties' => array_map(fn (ActivityDifficulty $d): string => $d->value, ActivityDifficulty::cases()),
                'durations' => Activity::DURATIONS,
                'answerTypes' => array_map(fn (AnswerType $t): string => $t->value, ActivityRequest::ANSWER_TYPES),
                'maxItems' => ActivityRequest::MAX_ITEMS,
                'maxChoices' => ActivityRequest::MAX_CHOICES,
            ],
            'libraryUrl' => route($this->library($user)),
        ]);
    }

    // Activité sous la forme du formulaire (une question « à plat »).
    /** @return array<string, mixed> */
    private function form(Activity $activity): array
    {
        return [
            'id' => $activity->id,
            'status' => $activity->status->value,
            'title' => $activity->title,
            'subject' => $activity->subject->key,
            'grade' => $activity->skill->grade_min?->value,
            'skill_id' => $activity->skill_id,
            'universe' => $activity->universe?->key,
            'format' => $activity->format->value,
            'difficulty' => $activity->difficulty->value,
            'duration_minutes' => $activity->duration_minutes,
            'learning_objective' => $activity->learning_objective,
            'items' => $activity->items->map(fn (ActivityItem $item): array => [
                'answer_type' => $item->answer_type->value,
                'prompt' => $item->prompt,
                'hint' => $item->hint,
                'explanation' => $item->explanation,
                'value' => $item->expected_answer['value'] ?? null,
                'unit' => $item->expected_answer['unit'] ?? null,
                'tolerance' => $item->tolerance === null ? null : (float) $item->tolerance,
                'accepted' => $item->expected_answer['accepted'] ?? [],
                'choices' => $item->prompt_payload['choices'] ?? [],
                'correct' => $item->expected_answer['index'] ?? null,
                'correct_many' => $item->expected_answer['indexes'] ?? [],
            ])->all(),
        ];
    }

    private function library(User $user): string
    {
        return $user->hasRole(Role::Professional->value) ? 'pro.library' : 'parent.library';
    }
}
