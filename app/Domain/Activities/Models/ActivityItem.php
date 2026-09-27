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

    /** @return BelongsTo<Activity, $this> */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
