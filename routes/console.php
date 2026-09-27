<?php

declare(strict_types=1);

use App\Domain\Activities\Models\Activity;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Journal d'audit : rétention d'un an (config/activitylog.php, clean_after_days).
Schedule::command('activitylog:clean --force')->daily();

// Corbeille des activités : effacement définitif après 30 jours (Activity::prunable()).
Schedule::command('model:prune', ['--model' => [Activity::class]])->daily();
