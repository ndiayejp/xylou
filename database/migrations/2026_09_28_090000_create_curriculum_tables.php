<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Référentiel (ADR 0016) : matières et compétences chargées depuis database/data/curriculum
// (commande curriculum:sync) ; univers d'illustration créés ici, rattachés aux passions.
return new class extends Migration
{
    /** @var array<string, list<string>> univers => passions qu'il illustre */
    private const array UNIVERSES = [
        'space' => ['space'],
        'football' => ['football'],
        'forest' => ['animals', 'nature'],
    ];

    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 30)->unique();
            $table->string('color_token', 30);
            $table->string('icon_key', 40);
            $table->unsignedSmallInteger('position');
            $table->timestamps();
        });

        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('parent_skill_id')->nullable()->constrained('skills')->restrictOnDelete();
            $table->string('code', 60)->unique();
            $table->string('label', 160);
            $table->text('description')->nullable();
            $table->string('grade_min', 3)->nullable();
            $table->string('grade_max', 3)->nullable();
            $table->unsignedSmallInteger('position');
            $table->timestamp('retired_at')->nullable();
            $table->timestamps();

            $table->index(['subject_id', 'parent_skill_id']);
        });

        Schema::create('universes', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 30)->unique();
            $table->string('illustration_set', 30);
            $table->unsignedSmallInteger('position');
        });

        Schema::table('interests', function (Blueprint $table): void {
            $table->foreignId('universe_id')->nullable()->constrained()->nullOnDelete();
        });

        $position = 0;
        foreach (self::UNIVERSES as $key => $interests) {
            $id = DB::table('universes')->insertGetId([
                'key' => $key, 'illustration_set' => $key, 'position' => ++$position,
            ]);
            DB::table('interests')->whereIn('key', $interests)->update(['universe_id' => $id]);
        }
    }

    public function down(): void
    {
        Schema::table('interests', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('universe_id');
        });
        Schema::dropIfExists('universes');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('subjects');
    }
};
