<?php

declare(strict_types=1);

namespace App\Domain\Children\Actions;

use App\Domain\Children\Data\ChildProfileInput;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Children\Models\Onboarding;
use Illuminate\Support\Facades\DB;

// Écran 2 : crée le profil au premier passage, le met à jour ensuite (retour arrière sans perte).
final readonly class SaveOnboardingChild
{
    public function __construct(
        private CreateChildProfile $createChildProfile,
        private UpdateChildProfile $updateChildProfile,
        private AdvanceOnboarding $advanceOnboarding,
    ) {}

    public function __invoke(Onboarding $onboarding, ChildProfileInput $input): ChildProfile
    {
        return DB::transaction(function () use ($onboarding, $input): ChildProfile {
            $child = $onboarding->child;

            if ($child instanceof ChildProfile) {
                ($this->updateChildProfile)($child, $input);
            } else {
                $child = ($this->createChildProfile)($onboarding->parent, $input);
                $onboarding->child()->associate($child);
            }

            ($this->advanceOnboarding)($onboarding, Onboarding::FIRST_STEP);

            return $child;
        });
    }
}
