<?php

declare(strict_types=1);

namespace App\Domain\Activities\Enums;

// Machine à états d'une activité (§6.3). L'affectation à un enfant vit dans « assignments » (étape 6).
enum ActivityStatus: string
{
    case Generating = 'generating';
    case GenerationFailed = 'generation_failed';
    case PendingReview = 'pending_review';
    case Draft = 'draft';
    case Approved = 'approved';
    case Archived = 'archived';

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Generating => [self::PendingReview, self::GenerationFailed],
            self::GenerationFailed => [self::Generating],
            self::PendingReview => [self::Approved, self::Draft, self::Generating],
            // Une activité écrite ou modifiée par l'adulte est approuvée en l'enregistrant.
            self::Draft => [self::PendingReview, self::Approved],
            self::Approved => [self::Archived],
            self::Archived => [self::Approved],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    // L'adulte modifie un brouillon ou une activité validée ; le reste passe par la relecture ou les archives.
    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::Approved], true);
    }

    // Une activité en cours de génération ou en échec n'a pas encore d'items.
    public function hasContent(): bool
    {
        return ! in_array($this, [self::Generating, self::GenerationFailed], true);
    }
}
