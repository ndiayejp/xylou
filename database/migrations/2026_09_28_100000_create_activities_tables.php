<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Activités et leurs items (§6.2). Clés ULID : non devinables dans les URL.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            // Sans enfant : modèle de bibliothèque.
            $table->foreignId('child_profile_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('skill_id')->constrained()->restrictOnDelete();
            $table->foreignId('universe_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 160);
            $table->string('format', 30);
            $table->string('difficulty', 20);
            $table->unsignedSmallInteger('duration_minutes');
            $table->string('status', 30);
            $table->string('source', 20);
            $table->text('learning_objective')->nullable();
            $table->text('context_summary')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_id', 'status']);
        });

        Schema::create('activity_items', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('activity_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->text('prompt');
            $table->jsonb('prompt_payload')->nullable();
            $table->string('answer_type', 30);
            $table->jsonb('expected_answer');
            $table->decimal('tolerance', 10, 4)->nullable();
            $table->text('hint')->nullable();
            $table->text('explanation')->nullable();
            $table->jsonb('common_errors')->nullable();
            $table->timestamps();

            $table->unique(['activity_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_items');
        Schema::dropIfExists('activities');
    }
};
