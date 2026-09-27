<?php

declare(strict_types=1);

namespace App\Domain\Activities\Models;

use App\Domain\Activities\Enums\AnswerType;
use Database\Factories\ActivityItemFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Une question de l'activité.
 *
 * @property string $id ULID
 * @property string $activity_id
 * @property int $position à partir de 1
 * @property string $prompt
 * @property array<string, mixed>|null $prompt_payload données structurées, ex. {distance: 240, minutes: 3}
 * @property AnswerType $answer_type
 * @property array<array-key, mixed> $expected_answer
 * @property string|null $tolerance
 * @property string|null $hint
 * @property string|null $explanation
 * @property list<array{answer: mixed, message: string}>|null $common_errors
 */
#[UseFactory(ActivityItemFactory::class)]
class ActivityItem extends Model
{
    /** @use HasFactory<ActivityItemFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'position', 'prompt', 'prompt_payload', 'answer_type', 'expected_answer',
        'tolerance', 'hint', 'explanation', 'common_errors',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'prompt_payload' => 'array',
            'answer_type' => AnswerType::class,
            'expected_answer' => 'array',
            'tolerance' => 'decimal:4',
            'common_errors' => 'array',
        ];
    }

    /**
     * Question prête pour l'enfant : énoncé, explication (montrée avec « Pas encore ») et réponse
     * attendue cohérente avec son type. Formes (éditeur, PR 5) :
     * nombre {value, unit} ; texte {accepted: [..]} ; choix {index} ou {indexes: [..]} avec
     * prompt_payload {choices: [..]}.
     */
    public function isComplete(): bool
    {
        if (trim($this->prompt) === '' || trim((string) $this->explanation) === '') {
            return false;
        }

        $answer = $this->expected_answer;
        $choices = $this->choices();

        return match ($this->answer_type) {
            AnswerType::Number => is_int($answer['value'] ?? null) || is_float($answer['value'] ?? null),
            AnswerType::Text => $this->filledStrings($answer['accepted'] ?? null) !== [],
            AnswerType::SingleChoice => count($choices) >= 2
                && is_int($answer['index'] ?? null) && isset($choices[$answer['index']]),
            AnswerType::MultipleChoice => count($choices) >= 2
                && is_array($answer['indexes'] ?? null) && $answer['indexes'] !== []
                && array_diff($answer['indexes'], array_keys($choices)) === [],
            default => $answer !== [],
        };
    }

    /** @return list<string> propositions d'un choix, toutes remplies, sinon aucune */
    public function choices(): array
    {
        $choices = $this->filledStrings($this->prompt_payload['choices'] ?? null);

        return count($choices) === count((array) ($this->prompt_payload['choices'] ?? [])) ? $choices : [];
    }

    /** @return list<string> */
    private function filledStrings(mixed $values): array
    {
        return is_array($values)
            ? array_values(array_filter($values, fn (mixed $v): bool => is_string($v) && trim($v) !== ''))
            : [];
    }

    /** @return BelongsTo<Activity, $this> */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
