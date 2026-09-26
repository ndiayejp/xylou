<?php

declare(strict_types=1);

namespace Database\Seeders;

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
        // Comptes de démonstration, mot de passe « password ». Les pros viendront par invitation (étape 9).
        User::factory()->parent()->create([
            'name' => 'Sophie Parent',
            'email' => 'parent@example.com',
        ]);

        User::factory()->professional()->create([
            'name' => 'Claire Pro',
            'email' => 'pro@example.com',
        ]);
    }
}
