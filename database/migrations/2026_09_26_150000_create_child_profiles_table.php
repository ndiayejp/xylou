<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Minimisation (§11.1) : prénom et niveau obligatoires, année de naissance seulement, pas de photo.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('first_name', 50);
            $table->unsignedSmallInteger('birth_year')->nullable();
            $table->string('grade', 3);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_profiles');
    }
};
