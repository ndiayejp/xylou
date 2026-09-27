<?php

declare(strict_types=1);

namespace App\Domain\Activities\Data;

use App\Domain\Activities\Enums\AnswerType;

final readonly class ActivityItemInput
{
    /**
     * @param  array<array-key, mixed>  $expectedAnswer
     * @param  array<string, mixed>|null  $promptPayload  ex. {choices: [...]} pour un choix
     * @param  list<array{answer: mixed, message: string}>  $commonErrors
     */
    public function __construct(
        public string $prompt,
        public AnswerType $answerType,
        public array $expectedAnswer,
        public ?float $tolerance = null,
        public ?string $hint = null,
        public ?string $explanation = null,
        public array $commonErrors = [],
        public ?array $promptPayload = null,
    ) {}

    /** @return array<string, mixed> */
    public function attributes(int $position): array
    {
        return [
            'position' => $position,
            'prompt' => $this->prompt,
            'prompt_payload' => $this->promptPayload,
            'answer_type' => $this->answerType,
            'expected_answer' => $this->expectedAnswer,
            'tolerance' => $this->tolerance,
            'hint' => $this->hint,
            'explanation' => $this->explanation,
            'common_errors' => $this->commonErrors === [] ? null : $this->commonErrors,
        ];
    }
}
