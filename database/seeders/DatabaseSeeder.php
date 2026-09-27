<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Activities\Enums\ActivityStatus;
use App\Domain\Activities\Models\Activity;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Curriculum\Actions\SyncCurriculum;
use App\Domain\Curriculum\Enums\Grade;
use App\Domain\Curriculum\Models\Skill;
use App\Domain\Curriculum\Models\Universe;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        resolve(SyncCurriculum::class)(database_path('data/curriculum'));

        // Comptes de démonstration, mot de passe « password ». Les pros viendront par invitation (étape 9).
        $parent = User::factory()->parent()->create([
            'name' => 'Sophie Parent',
            'email' => 'parent@example.com',
        ]);

        ChildProfile::factory()->for($parent, 'owner')->createMany([
            ['first_name' => 'Emma', 'birth_year' => 2018, 'grade' => Grade::Ce2],
            ['first_name' => 'Lucas', 'birth_year' => 2015, 'grade' => Grade::Sixieme],
        ]);

        $this->library($parent);

        User::factory()->professional()->create([
            'name' => 'Claire Pro',
            'email' => 'pro@example.com',
        ]);
    }

    // Bibliothèque de démonstration (niveau CE2, celui d'Emma).
    private function library(User $parent): void
    {
        // Une compétence différente par activité, dans l’ordre des codes.
        $skill = fn (string $subject, int $rank): Skill => Skill::query()->active()->forGrade(Grade::Ce2)
            ->whereRelation('subject', 'key', $subject)->orderBy('code')->skip($rank)->firstOrFail();
        $universe = fn (string $key): ?int => Universe::query()->where('key', $key)->value('id');

        foreach ([
            ['Le ravitaillement de la fusée', 'maths', 'space', ActivityStatus::PendingReview, 10],
            ['Mission Mars', 'maths', 'space', ActivityStatus::Approved, 10],
            ['La fusée des tables', 'maths', 'space', ActivityStatus::Approved, 5],
            ['Le renard compte ses pas', 'maths', 'forest', ActivityStatus::Draft, 10],
            ['Tirs au but multiplicatifs', 'maths', 'football', ActivityStatus::Archived, 10],
            ['Le journal de bord du capitaine', 'french', 'space', ActivityStatus::Approved, 15],
        ] as $rank => [$title, $subject, $key, $status, $minutes]) {
            $picked = $skill($subject, $rank);
            // WithoutModelEvents coupe l'indexation automatique de Scout : on indexe à la main.
            Activity::factory()->for($parent, 'owner')->for($picked)->status($status)->create([
                'subject_id' => $picked->subject_id,
                'universe_id' => $universe($key),
                'title' => $title,
                'duration_minutes' => $minutes,
            ])->searchable();
        }
    }
}
