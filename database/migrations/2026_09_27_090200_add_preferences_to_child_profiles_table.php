<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Écran 2 de l'onboarding : langue d'apprentissage, avatar, options de confort (§6.2).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('child_profiles', function (Blueprint $table): void {
            $table->string('language', 5)->default('fr')->after('grade');
            $table->string('avatar_key', 20)->nullable()->after('language');
            $table->json('comfort_settings')->nullable()->after('avatar_key');
        });
    }

    public function down(): void
    {
        Schema::table('child_profiles', function (Blueprint $table): void {
            $table->dropColumn(['language', 'avatar_key', 'comfort_settings']);
        });
    }
};
