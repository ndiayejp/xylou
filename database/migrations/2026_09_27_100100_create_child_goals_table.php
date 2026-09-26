<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Écran 4 « Objectifs » : plusieurs objectifs, un seul principal ; ils orientent les recommandations.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_goals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('child_profile_id')->constrained()->cascadeOnDelete();
            $table->string('goal', 30);
            $table->boolean('is_primary')->default(false);
            $table->date('target_date')->nullable();
            $table->string('note', 200)->nullable();
            $table->timestamps();

            $table->unique(['child_profile_id', 'goal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_goals');
    }
};
