<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Activities\Data\LibraryFilters;
use App\Domain\Activities\Enums\ActivityDifficulty;
use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Models\Activity;
use App\Domain\Activities\Queries\InterpretLibrarySearch;
use App\Domain\Curriculum\Enums\Grade;
use Illuminate\Foundation\Http\FormRequest;

// Filtres de la bibliothèque, lus dans l'URL. Une valeur invalide est ignorée plutôt que refusée :
// un lien ancien ou retouché affiche la bibliothèque sans ce filtre.
final class LibraryRequest extends FormRequest
{
    public const string TRASH = 'deleted';

    private const array KEYS = ['subject', 'skill', 'grade', 'duration', 'difficulty', 'universe', 'status', 'q', 'ask'];

    // Un tableau dans l'adresse (« subject[]=… ») compte comme un filtre absent.
    protected function prepareForValidation(): void
    {
        $this->merge(array_map(
            fn (mixed $value): mixed => is_array($value) ? null : $value,
            $this->only(self::KEYS),
        ));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }

    // « grade » absent de l'URL : niveau par défaut (classe de l'enfant courant) ; vide : aucun niveau.
    public function filters(?Grade $defaultGrade = null): LibraryFilters
    {
        $status = $this->string('status')->value();

        return new LibraryFilters(
            subject: $this->filled('subject') ? $this->string('subject')->value() : null,
            skillId: $this->filled('skill') && ctype_digit($this->string('skill')->value()) ? $this->integer('skill') : null,
            grade: $this->has('grade') ? Grade::tryFrom($this->string('grade')->value()) : $defaultGrade,
            duration: in_array($this->string('duration')->value(), self::durations(), true) ? $this->string('duration')->value() : null,
            difficulty: ActivityDifficulty::tryFrom($this->string('difficulty')->value()),
            universe: $this->filled('universe') ? $this->string('universe')->value() : null,
            status: ActivityStatus::tryFrom($status),
            trash: $status === self::TRASH,
            text: $this->string('q')->squish()->limit(100, '')->value(),
        );
    }

    /** @return array<string, string> valeurs à renvoyer à la page ('' : filtre vide) */
    public static function values(LibraryFilters $filters): array
    {
        return [
            'subject' => $filters->subject ?? '',
            'skill' => $filters->skillId === null ? '' : (string) $filters->skillId,
            'grade' => $filters->grade->value ?? '',
            'duration' => $filters->duration ?? '',
            'difficulty' => $filters->difficulty->value ?? '',
            'universe' => $filters->universe ?? '',
            'status' => $filters->trash ? self::TRASH : ($filters->status->value ?? ''),
            'q' => $filters->text,
        ];
    }

    /** @return list<string> */
    public static function durations(): array
    {
        return [
            ...array_map(strval(...), Activity::DURATIONS),
            InterpretLibrarySearch::SHORT,
            InterpretLibrarySearch::LONG,
        ];
    }
}
