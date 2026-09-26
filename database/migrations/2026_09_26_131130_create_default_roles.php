<?php

declare(strict_types=1);

use App\Domain\Identity\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Les rôles sont des données de référence : présents dans tous les environnements, tests compris.
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('roles')->insert(array_map(fn (Role $role): array => [
            'name' => $role->value,
            'guard_name' => 'web',
            'created_at' => $now,
            'updated_at' => $now,
        ], Role::cases()));
    }

    public function down(): void
    {
        DB::table('roles')->whereIn('name', array_column(Role::cases(), 'value'))->delete();
    }
};
