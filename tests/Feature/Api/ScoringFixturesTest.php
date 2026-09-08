<?php

namespace Tests\Feature\Api;

use App\Services\Learning\MissionScoringService;
use Tests\TestCase;

/**
 * The PHP half of the scoring contract.
 *
 * The Dart half is mobile/test/scoring_test.dart, run against these same files.
 * If the two ever disagree, one of these suites goes red before a child sees
 * their stars change on sync.
 */
class ScoringFixturesTest extends TestCase
{
    protected function fixtures(string $name): array
    {
        $path = base_path("fixtures/scoring/{$name}");

        $this->assertFileExists($path, 'The shared scoring fixtures are missing.');

        return json_decode((string) file_get_contents($path), true)['cases'] ?? [];
    }

    public function test_every_question_fixture_scores_as_expected(): void
    {
        $scoring = app(MissionScoringService::class);
        $cases = $this->fixtures('questions.json');

        $this->assertNotEmpty($cases);

        foreach ($cases as $case) {
            $this->assertSame(
                $case['expected'],
                $scoring->isCorrect($case['question'], $case['answer']),
                $case['name']
            );
        }
    }

    public function test_every_mission_fixture_scores_as_expected(): void
    {
        $scoring = app(MissionScoringService::class);
        $cases = $this->fixtures('missions.json');

        $this->assertNotEmpty($cases);

        foreach ($cases as $case) {
            $result = $scoring->score(
                $case['answers'],
                $case['questions'],
                (int) ($case['pass_threshold_percent'] ?? 60),
                isset($case['cap']) ? (int) $case['cap'] : null
            );

            $this->assertSame(
                $case['expected'],
                [
                    'score' => $result->score,
                    'total' => $result->total,
                    'percentage' => $result->percentage,
                    'stars' => $result->stars,
                    'passed' => $result->passed,
                ],
                $case['name']
            );
        }
    }

    public function test_a_forged_perfect_claim_scores_only_what_was_answered(): void
    {
        $scoring = app(MissionScoringService::class);

        $questions = [
            ['id' => 1, 'type' => 'multiple_choice', 'options' => [
                ['id' => 10, 'is_correct' => true, 'sort_order' => 1],
                ['id' => 11, 'is_correct' => false, 'sort_order' => 2],
            ]],
        ];

        $result = $scoring->score([['question_id' => 1, 'response' => ['option_id' => 11]]], $questions);

        $this->assertSame(0, $result->score);
        $this->assertSame(0, $result->stars);
        $this->assertFalse($result->passed);
    }
}
