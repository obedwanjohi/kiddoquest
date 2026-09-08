<?php

namespace App\Console\Commands;

use App\Services\Learning\MissionScoringService;
use Illuminate\Console\Command;

/**
 * Runs the shared scoring fixtures against the PHP scorer.
 *
 * The Dart test suite in mobile/test/scoring_test.dart runs the same files, so
 * if this command passes and that test passes, the server and the app agree on
 * every question type. Kept as a command rather than only a PHPUnit test so it
 * can run in a deployment check where dev dependencies are not installed.
 */
class VerifyScoringFixtures extends Command
{
    protected $signature = 'scoring:verify {--path= : Directory holding the fixture files}';

    protected $description = 'Check the scoring service against the shared fixtures in fixtures/scoring';

    public function handle(MissionScoringService $scoring): int
    {
        $dir = rtrim($this->option('path') ?: base_path('fixtures/scoring'), '/\\');
        $failures = [];
        $passed = 0;

        foreach ($this->load("{$dir}/questions.json") as $case) {
            $actual = $scoring->isCorrect($case['question'], $case['answer']);
            $expected = $case['expected'];

            if ($actual === $expected) {
                $passed++;
            } else {
                $failures[] = sprintf(
                    '%s: expected %s, got %s',
                    $case['name'],
                    var_export($expected, true),
                    var_export($actual, true)
                );
            }
        }

        foreach ($this->load("{$dir}/missions.json") as $case) {
            $result = $scoring->score(
                $case['answers'],
                $case['questions'],
                (int) ($case['pass_threshold_percent'] ?? 60),
                isset($case['cap']) ? (int) $case['cap'] : null
            );

            $actual = [
                'score'      => $result->score,
                'total'      => $result->total,
                'percentage' => $result->percentage,
                'stars'      => $result->stars,
                'passed'     => $result->passed,
            ];

            if ($actual === $case['expected']) {
                $passed++;
            } else {
                $failures[] = sprintf(
                    "%s:\n    expected %s\n    got      %s",
                    $case['name'],
                    json_encode($case['expected']),
                    json_encode($actual)
                );
            }
        }

        foreach ($failures as $failure) {
            $this->error('  ' . $failure);
        }

        $this->newLine();

        if ($failures === []) {
            $this->info("Scoring fixtures: {$passed} passed.");

            return self::SUCCESS;
        }

        $this->error(sprintf('Scoring fixtures: %d passed, %d failed.', $passed, count($failures)));

        return self::FAILURE;
    }

    protected function load(string $path): array
    {
        if (! is_file($path)) {
            $this->warn("Missing fixture file: {$path}");

            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);

        return $data['cases'] ?? [];
    }
}
