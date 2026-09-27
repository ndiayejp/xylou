<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Activities\Data\ActivityInput;
use App\Domain\Activities\Data\ActivityItemInput;
use App\Domain\Activities\Enums\ActivityDifficulty;
use App\Domain\Activities\Enums\ActivityFormat;
use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Enums\AnswerType;
use App\Domain\Activities\Models\Activity;
use App\Domain\Curriculum\Models\Skill;
use App\Domain\Curriculum\Models\Universe;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Éditeur d'activité. « intent » : draft (questions incomplètes permises) ou approve (tout doit
 * être prêt pour l'enfant). Une question arrive à plat ; elle devient la forme stockée dans items().
 */
final class ActivityRequest extends FormRequest
{
    public const array ANSWER_TYPES = [AnswerType::Number, AnswerType::Text, AnswerType::SingleChoice, AnswerType::MultipleChoice];

    public const int MAX_ITEMS = 20;

    public const int MAX_CHOICES = 6;

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'intent' => ['required', Rule::in(['draft', 'approve'])],
            'title' => ['required', 'string', 'max:160'],
            'skill_id' => ['required', 'integer', Rule::exists('skills', 'id')->whereNull('retired_at')->whereNotNull('parent_skill_id')],
            'universe' => ['nullable', 'string', Rule::exists('universes', 'key')],
            'format' => ['required', Rule::enum(ActivityFormat::class)],
            'difficulty' => ['required', Rule::enum(ActivityDifficulty::class)],
            'duration_minutes' => ['required', 'integer', Rule::in(Activity::DURATIONS)],
            'learning_objective' => ['nullable', 'string', 'max:500'],
            'items' => [$this->approving() ? 'required' : 'present', 'array', 'max:'.self::MAX_ITEMS],
            'items.*.answer_type' => ['required', Rule::in(array_map(fn (AnswerType $t): string => $t->value, self::ANSWER_TYPES))],
            'items.*.prompt' => ['nullable', 'string', 'max:1000'],
            'items.*.hint' => ['nullable', 'string', 'max:500'],
            'items.*.explanation' => ['nullable', 'string', 'max:1000'],
            'items.*.value' => ['nullable', 'numeric'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
            'items.*.tolerance' => ['nullable', 'numeric', 'min:0'],
            'items.*.accepted' => ['nullable', 'array', 'max:10'],
            'items.*.accepted.*' => ['nullable', 'string', 'max:100'],
            'items.*.choices' => ['nullable', 'array', 'max:'.self::MAX_CHOICES],
            'items.*.choices.*' => ['nullable', 'string', 'max:200'],
            'items.*.correct' => ['nullable', 'integer', 'min:0'],
            'items.*.correct_many' => ['nullable', 'array'],
            'items.*.correct_many.*' => ['integer', 'min:0'],
        ];
    }

    // Pour valider, chaque question doit être prête : les erreurs visent le champ à compléter.
    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->approving() || $validator->errors()->has('items')) {
                return;
            }

            // Tout est signalé d'un coup, sauf les questions au type de réponse invalide.
            foreach ($this->itemsInput() as $i => $item) {
                if (! $validator->errors()->has("items.{$i}.answer_type")) {
                    $this->checkItem($validator, "items.{$i}", $item);
                }
            }
        }];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'title' => __('activities.fields.title'),
            'skill_id' => __('activities.fields.skill'),
            'items' => __('activities.fields.items'),
            'items.*.prompt' => __('activities.fields.prompt'),
            'items.*.explanation' => __('activities.fields.explanation'),
            'items.*.value' => __('activities.fields.value'),
            'items.*.tolerance' => __('activities.fields.tolerance'),
        ];
    }

    // Une activité déjà validée le reste : elle doit rester complète.
    public function approving(): bool
    {
        $activity = $this->route('activity');

        return $this->input('intent') === 'approve'
            || ($activity instanceof Activity && $activity->status === ActivityStatus::Approved);
    }

    public function activity(): ActivityInput
    {
        $universe = $this->filled('universe')
            ? Universe::query()->where('key', $this->string('universe')->value())->first()
            : null;

        return new ActivityInput(
            title: $this->string('title')->trim()->value(),
            skill: Skill::query()->findOrFail($this->integer('skill_id')),
            format: ActivityFormat::from($this->string('format')->value()),
            difficulty: ActivityDifficulty::from($this->string('difficulty')->value()),
            durationMinutes: $this->integer('duration_minutes'),
            universe: $universe,
            learningObjective: $this->filled('learning_objective') ? $this->string('learning_objective')->trim()->value() : null,
            items: array_map($this->item(...), $this->itemsInput()),
        );
    }

    /** @param array<string, mixed> $item */
    private function item(array $item): ActivityItemInput
    {
        $type = AnswerType::from((string) $item['answer_type']);
        $choices = $this->strings($item['choices'] ?? []);
        $multiple = array_map(intval(...), (array) ($item['correct_many'] ?? []));
        sort($multiple);

        [$expected, $payload] = match ($type) {
            AnswerType::Number => [[
                'value' => is_numeric($item['value'] ?? null) ? 0 + $item['value'] : null,
                'unit' => $this->text($item['unit'] ?? null),
            ], null],
            AnswerType::Text => [['accepted' => array_values(array_filter($this->strings($item['accepted'] ?? []), fn (string $a): bool => $a !== ''))], null],
            AnswerType::SingleChoice => [['index' => isset($item['correct']) ? (int) $item['correct'] : null], ['choices' => $choices]],
            default => [['indexes' => array_values(array_unique($multiple))], ['choices' => $choices]],
        };

        return new ActivityItemInput(
            prompt: $this->text($item['prompt'] ?? null) ?? '',
            answerType: $type,
            expectedAnswer: $expected,
            tolerance: $type === AnswerType::Number && is_numeric($item['tolerance'] ?? null) ? (float) $item['tolerance'] : null,
            hint: $this->text($item['hint'] ?? null),
            explanation: $this->text($item['explanation'] ?? null),
            promptPayload: $payload,
        );
    }

    /** @param array<string, mixed> $item */
    private function checkItem(Validator $validator, string $key, array $item): void
    {
        $missing = [];
        if ($this->text($item['prompt'] ?? null) === null) {
            $missing[] = 'prompt';
        }
        if ($this->text($item['explanation'] ?? null) === null) {
            $missing[] = 'explanation';
        }

        $choices = $this->strings($item['choices'] ?? []);
        $choicesFilled = count($choices) >= 2 && ! in_array('', $choices, true);
        $many = array_map(intval(...), (array) ($item['correct_many'] ?? []));

        $missing[] = match ((string) $item['answer_type']) {
            AnswerType::Number->value => is_numeric($item['value'] ?? null) ? null : 'value',
            AnswerType::Text->value => array_filter($this->strings($item['accepted'] ?? [])) === [] ? 'accepted' : null,
            AnswerType::SingleChoice->value => match (true) {
                ! $choicesFilled => 'choices',
                ! isset($item['correct']) || (int) $item['correct'] >= count($choices) => 'correct',
                default => null,
            },
            default => match (true) {
                ! $choicesFilled => 'choices',
                $many === [] || max($many) >= count($choices) => 'correct_many',
                default => null,
            },
        };

        foreach (array_filter($missing) as $field) {
            $validator->errors()->add("{$key}.{$field}", __("activities.editor.{$field}"));
        }
    }

    /** @return list<array<string, mixed>> */
    private function itemsInput(): array
    {
        $items = $this->input('items', []);

        return is_array($items) ? array_values(array_filter($items, is_array(...))) : [];
    }

    /** @return list<string> */
    private function strings(mixed $values): array
    {
        return is_array($values) ? array_values(array_map(fn (mixed $v): string => trim((string) $v), $values)) : [];
    }

    private function text(mixed $value): ?string
    {
        $text = is_scalar($value) ? trim((string) $value) : '';

        return $text === '' ? null : $text;
    }
}
