<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Écran 5 « Difficultés » (privé, jamais montré à l'enfant) et écran 6 « Préférences » (§6.2).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_difficulties', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('child_profile_id')->constrained()->cascadeOnDelete();
            $table->string('subject', 20);
            $table->string('key', 40);
            // Rattachement au référentiel de compétences (étape 4).
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->timestamps();

            $table->unique(['child_profile_id', 'subject', 'key']);
        });

        // Une seule observation libre par enfant (maquette) : chiffrée (cast « encrypted »).
        Schema::table('child_profiles', function (Blueprint $table): void {
            $table->text('difficulty_observation')->nullable()->after('comfort_settings');
        });

        Schema::create('learning_preferences', function (Blueprint $table): void {
            $table->foreignId('child_profile_id')->primary()->constrained()->cascadeOnDelete();
            $table->boolean('visual')->default(false);
            $table->boolean('concrete_examples')->default(false);
            $table->boolean('small_steps')->default(false);
            $table->boolean('repetition')->default(false);
            $table->boolean('short_explanations')->default(false);
            $table->boolean('interactive')->default(false);
            $table->unsignedTinyInteger('session_minutes')->default(10);
            $table->boolean('gentle_reminder')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_preferences');
        Schema::table('child_profiles', function (Blueprint $table): void {
            $table->dropColumn('difficulty_observation');
        });
        Schema::dropIfExists('child_difficulties');
    }
};
