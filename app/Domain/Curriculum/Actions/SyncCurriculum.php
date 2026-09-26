<?php

declare(strict_types=1);

namespace App\Domain\Curriculum\Actions;

use App\Domain\Curriculum\Enums\Grade;
use App\Domain\Curriculum\Exceptions\InvalidCurriculum;
use App\Domain\Curriculum\Models\Skill;
use App\Domain\Curriculum\Models\Subject;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Yaml\Yaml;

/**
 * Charge le référentiel depuis les fichiers YAML (un par matière) : crée ou met à jour matières,
 * domaines et compétences par leur clé ou leur code. Idempotent. Une compétence absente des
 * fichiers est retirée (retired_at), jamais supprimée. Tout est validé avant la moindre écriture.
 */
final class SyncCurriculum
{
    private const string CODE = '/^[A-Z0-9]+(\.[A-Z0-9]+)+$/';

    /** @return array{subjects: int, skills: int, retired: int} */
    public function __invoke(string $directory): array
    {
        $files = glob(rtrim($directory, '/\\').'/*.yaml') ?: [];
        if ($files === []) {
            throw new InvalidCurriculum('Aucun fichier de référentiel dans '.$directory);
        }

        $subjects = [];
        $codes = [];
        foreach ($files as $file) {
            $subject = $this->parse($file);
            foreach ($subject['domains'] as $domain) {
                foreach ([$domain['code'], ...array_column($domain['skills'], 'code')] as $code) {
                    if (isset($codes[$code])) {
                        throw InvalidCurriculum::in($file, "le code {$code} existe déjà ({$codes[$code]}).");
                    }
                    $codes[$code] = basename($file);
                }
            }
            $subjects[] = $subject;
        }

        return DB::transaction(function () use ($subjects): array {
            $report = ['subjects' => 0, 'skills' => 0, 'retired' => 0];

            foreach ($subjects as $data) {
                $subject = Subject::query()->updateOrCreate(['key' => $data['key']], [
                    'color_token' => $data['color'],
                    'icon_key' => $data['icon'],
                    'position' => $data['position'],
                ]);
                $report['subjects']++;

                $kept = [];
                foreach ($data['domains'] as $domainPosition => $domain) {
                    $parent = $this->save($subject, null, $domain['code'], $domain['label'], null, null, $domainPosition + 1);
                    $kept[] = $parent->id;

                    foreach ($domain['skills'] as $position => $skill) {
                        $kept[] = $this->save(
                            $subject,
                            $parent,
                            $skill['code'],
                            $skill['label'],
                            $skill['description'],
                            $skill['grades'],
                            $position + 1,
                        )->id;
                        $report['skills']++;
                    }
                }

                $report['retired'] += Skill::query()
                    ->where('subject_id', $subject->id)
                    ->whereNotIn('id', $kept)
                    ->whereNull('retired_at')
                    ->update(['retired_at' => now()]);
            }

            return $report;
        });
    }

    /** @param array{0: Grade, 1: Grade}|null $grades */
    private function save(
        Subject $subject,
        ?Skill $parent,
        string $code,
        string $label,
        ?string $description,
        ?array $grades,
        int $position,
    ): Skill {
        return Skill::query()->updateOrCreate(['code' => $code], [
            'subject_id' => $subject->id,
            'parent_skill_id' => $parent?->id,
            'label' => $label,
            'description' => $description,
            'grade_min' => $grades[0] ?? null,
            'grade_max' => $grades[1] ?? null,
            'position' => $position,
            'retired_at' => null,
        ]);
    }

    /**
     * @return array{key: string, color: string, icon: string, position: int, domains: list<array{
     *     code: string, label: string,
     *     skills: list<array{code: string, label: string, description: string|null, grades: array{0: Grade, 1: Grade}}>
     * }>}
     */
    private function parse(string $file): array
    {
        $yaml = Yaml::parseFile($file);
        if (! is_array($yaml) || ! is_array($yaml['subject'] ?? null)) {
            throw InvalidCurriculum::in($file, 'la clé « subject » est obligatoire.');
        }

        $subject = $yaml['subject'];
        foreach (['key', 'color', 'icon'] as $field) {
            if (! is_string($subject[$field] ?? null) || $subject[$field] === '') {
                throw InvalidCurriculum::in($file, "« subject.{$field} » est obligatoire.");
            }
        }
        if (! is_int($subject['position'] ?? null)) {
            throw InvalidCurriculum::in($file, '« subject.position » doit être un entier.');
        }

        $domains = $yaml['domains'] ?? null;
        if (! is_array($domains) || $domains === [] || ! array_is_list($domains)) {
            throw InvalidCurriculum::in($file, '« domains » doit être une liste non vide.');
        }

        return [
            'key' => $subject['key'],
            'color' => $subject['color'],
            'icon' => $subject['icon'],
            'position' => $subject['position'],
            'domains' => array_map(fn (mixed $domain): array => $this->domain($file, $domain), $domains),
        ];
    }

    /** @return array{code: string, label: string, skills: list<array{code: string, label: string, description: string|null, grades: array{0: Grade, 1: Grade}}>} */
    private function domain(string $file, mixed $domain): array
    {
        if (! is_array($domain)) {
            throw InvalidCurriculum::in($file, 'chaque domaine doit avoir un code, un libellé et des compétences.');
        }
        $code = $this->code($file, $domain['code'] ?? null);
        $skills = $domain['skills'] ?? null;
        if (! is_array($skills) || $skills === [] || ! array_is_list($skills)) {
            throw InvalidCurriculum::in($file, "le domaine {$code} n’a aucune compétence.");
        }

        return [
            'code' => $code,
            'label' => $this->label($file, $code, $domain['label'] ?? null),
            'skills' => array_map(fn (mixed $skill): array => $this->skill($file, $skill), $skills),
        ];
    }

    /** @return array{code: string, label: string, description: string|null, grades: array{0: Grade, 1: Grade}} */
    private function skill(string $file, mixed $skill): array
    {
        if (! is_array($skill)) {
            throw InvalidCurriculum::in($file, 'chaque compétence doit avoir un code, un libellé et des niveaux.');
        }
        $code = $this->code($file, $skill['code'] ?? null);
        $description = $skill['description'] ?? null;

        return [
            'code' => $code,
            'label' => $this->label($file, $code, $skill['label'] ?? null),
            'description' => is_string($description) && $description !== '' ? $description : null,
            'grades' => $this->grades($file, $code, $skill['grades'] ?? null),
        ];
    }

    private function code(string $file, mixed $code): string
    {
        if (! is_string($code) || strlen($code) > 60 || preg_match(self::CODE, $code) !== 1) {
            throw InvalidCurriculum::in($file, 'code invalide « '.(is_scalar($code) ? $code : '?').' » (ex. MATH.CE2.PROB.2STEPS).');
        }

        return $code;
    }

    private function label(string $file, string $code, mixed $label): string
    {
        if (! is_string($label) || trim($label) === '' || mb_strlen($label) > 160) {
            throw InvalidCurriculum::in($file, "{$code} : libellé obligatoire, 160 caractères au plus.");
        }

        return trim($label);
    }

    // « ce2 » ou « ce1-ce2 ».
    /** @return array{0: Grade, 1: Grade} */
    private function grades(string $file, string $code, mixed $grades): array
    {
        $bounds = is_string($grades) ? explode('-', $grades) : [];
        $min = Grade::tryFrom($bounds[0] ?? '');
        $max = Grade::tryFrom($bounds[1] ?? $bounds[0] ?? '');

        if (count($bounds) > 2 || ! $min instanceof Grade || ! $max instanceof Grade || $min->rank() > $max->rank()) {
            throw InvalidCurriculum::in($file, "{$code} : niveaux invalides (ex. « ce2 » ou « ce1-ce2 »).");
        }

        return [$min, $max];
    }
}
