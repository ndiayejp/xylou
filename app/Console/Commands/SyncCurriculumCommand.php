<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Curriculum\Actions\SyncCurriculum;
use App\Domain\Curriculum\Exceptions\InvalidCurriculum;
use Illuminate\Console\Command;

// À lancer à chaque déploiement et après toute modification des fichiers du référentiel.
final class SyncCurriculumCommand extends Command
{
    protected $signature = 'curriculum:sync {--path= : dossier des fichiers YAML (défaut : database/data/curriculum)}';

    protected $description = 'Charge le référentiel (matières, compétences) depuis les fichiers YAML';

    public function handle(SyncCurriculum $syncCurriculum): int
    {
        $path = $this->option('path');

        try {
            $report = $syncCurriculum(is_string($path) ? $path : database_path('data/curriculum'));
        } catch (InvalidCurriculum $invalidCurriculum) {
            $this->components->error($invalidCurriculum->getMessage());

            return self::FAILURE;
        }

        $this->components->info(sprintf(
            'Référentiel chargé : %d matières, %d compétences, %d retirées.',
            $report['subjects'],
            $report['skills'],
            $report['retired'],
        ));

        return self::SUCCESS;
    }
}
