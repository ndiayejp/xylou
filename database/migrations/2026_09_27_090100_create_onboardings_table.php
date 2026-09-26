<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Progression de l'onboarding d'un enfant (écrans 2 à 7) : sauvegarde à chaque étape, reprise possible.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboardings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('child_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('reached_step');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['parent_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboardings');
    }
};
