<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Écran 3 « Son univers » : référentiel des passions (libellés dans vue-i18n), choix de l'enfant,
// passions libres. Le rattachement aux univers d'illustration viendra avec le référentiel (étape 4).
return new class extends Migration
{
    /** @var array<string, string> clé => catégorie, dans l'ordre de la maquette */
    private const array INTERESTS = [
        'football' => 'sports',
        'space' => 'sciences',
        'lego' => 'activities',
        'animals' => 'animals',
        'dinosaurs' => 'animals',
        'music' => 'music',
        'drawing' => 'activities',
        'video_games' => 'games',
        'cars' => 'activities',
        'ocean' => 'animals',
        'horse_riding' => 'sports',
        'experiments' => 'sciences',
        'puzzles' => 'games',
        'singing_dancing' => 'music',
        'nature' => 'animals',
    ];

    public function up(): void
    {
        Schema::create('interests', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 40)->unique();
            $table->string('category', 20);
            $table->string('icon_key', 40);
            $table->unsignedSmallInteger('position');
        });

        Schema::create('child_interest', function (Blueprint $table): void {
            $table->foreignId('child_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interest_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rank');
            $table->primary(['child_profile_id', 'interest_id']);
        });

        Schema::create('custom_interests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('child_profile_id')->constrained()->cascadeOnDelete();
            $table->string('label', 40);
            $table->timestamps();
        });

        $position = 0;
        DB::table('interests')->insert(array_map(function (string $key, string $category) use (&$position): array {
            return ['key' => $key, 'category' => $category, 'icon_key' => $key, 'position' => ++$position];
        }, array_keys(self::INTERESTS), self::INTERESTS));
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_interests');
        Schema::dropIfExists('child_interest');
        Schema::dropIfExists('interests');
    }
};
