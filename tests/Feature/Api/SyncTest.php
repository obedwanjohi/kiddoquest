<?php

namespace Tests\Feature\Api;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\Mission;
use App\Models\MissionAttempt;
use App\Models\QuestionOption;
use App\Models\QuizQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The contract the app depends on: events land once, scores are the server's,
 * and a replayed outbox never pays a child twice.
 */
class SyncTest extends TestCase
{
    use RefreshDatabase;

    protected Guardian $guardian;

    protected Child $child;

    protected Mission $mission;

    /** @var array<int,QuizQuestion> */
    protected array $questions = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->guardian = Guardian::create([
            'name' => 'Test Parent',
            'email' => 'parent@test.local',
            'password' => 'password123',
            'parent_pin' => '1234',
            'is_active' => true,
        ]);

        $this->child = $this->guardian->children()->create([
            'name' => 'Test Child',
            'avatar' => 'lion',
            'recommended_level' => 'Play Group',
            'total_stars' => 0,
            'star_coins' => 0,
        ]);

        $this->mission = Mission::create([
            'title' => 'Counting Apples',
            'slug' => 'counting-apples-test',
            'status' => 'published',
            'questions_per_session' => 3,
            'pass_threshold_percent' => 60,
        ]);

        for ($i = 0; $i < 3; $i++) {
            $question = QuizQuestion::create([
                'type' => 'multiple_choice',
                'prompt' => "Question {$i}",
                'points' => 1,
            ]);

            QuestionOption::create([
                'question_id' => $question->id,
                'text_value' => 'right',
                'is_correct' => true,
                'sort_order' => 1,
            ]);

            QuestionOption::create([
                'question_id' => $question->id,
                'text_value' => 'wrong',
                'is_correct' => false,
                'sort_order' => 2,
            ]);

            $this->questions[] = $question->fresh('options');
        }

        Sanctum::actingAs($this->guardian, ['parent']);
    }

    protected function headers(): array
    {
        return [
            'X-Device-Id' => 'test-device',
            'X-Child-Id' => (string) $this->child->id,
            'Accept' => 'application/json',
        ];
    }

    protected function answer(int $index, bool $correct): array
    {
        $question = $this->questions[$index];
        $option = $question->options->firstWhere('is_correct', $correct);

        return [
            'question_id' => $question->id,
            'response' => ['option_id' => $option->id],
        ];
    }

    protected function completionEvent(array $answers, ?string $id = null, array $claims = []): array
    {
        return [
            'id' => $id ?? (string) Str::uuid(),
            'seq' => 1,
            'type' => 'mission_completed',
            'client_ts' => now()->toIso8601String(),
            'payload' => array_merge([
                'mission_id' => $this->mission->id,
                'time_spent' => 120,
                'answers' => $answers,
            ], $claims),
        ];
    }

    public function test_it_scores_a_mission_from_the_answers_not_the_claim(): void
    {
        $event = $this->completionEvent(
            [$this->answer(0, true), $this->answer(1, true), $this->answer(2, false)],
            claims: ['score' => 3, 'stars' => 3, 'total' => 3],
        );

        $response = $this->postJson('/api/v1/sync', ['events' => [$event]], $this->headers());

        $response->assertOk();
        $response->assertJsonPath('snapshot.child.total_stars', 2);

        $attempt = MissionAttempt::where('child_id', $this->child->id)->firstOrFail();

        $this->assertSame(2, $attempt->score, 'two of three answers were right');
        $this->assertSame(2, $attempt->stars);
        $this->assertTrue($attempt->passed);
    }

    public function test_a_replayed_event_does_not_pay_twice(): void
    {
        $event = $this->completionEvent([$this->answer(0, true), $this->answer(1, true), $this->answer(2, true)]);

        $first = $this->postJson('/api/v1/sync', ['events' => [$event]], $this->headers());
        $stars = $first->json('snapshot.child.total_stars');
        $coins = $first->json('snapshot.child.star_coins');

        $second = $this->postJson('/api/v1/sync', ['events' => [$event]], $this->headers());

        $second->assertOk();
        $second->assertJsonPath('snapshot.child.total_stars', $stars);
        $second->assertJsonPath('snapshot.child.star_coins', $coins);
        $this->assertSame(1, MissionAttempt::where('child_id', $this->child->id)->count());
    }

    public function test_replaying_an_event_is_still_reported_as_accepted(): void
    {
        $event = $this->completionEvent([$this->answer(0, true)]);

        $this->postJson('/api/v1/sync', ['events' => [$event]], $this->headers());
        $response = $this->postJson('/api/v1/sync', ['events' => [$event]], $this->headers());

        $response->assertJsonCount(1, 'accepted');
        $this->assertContains($event['id'], $response->json('accepted'));
    }

    public function test_only_the_best_attempt_adds_stars(): void
    {
        $this->postJson('/api/v1/sync', [
            'events' => [$this->completionEvent([$this->answer(0, true), $this->answer(1, true), $this->answer(2, true)])],
        ], $this->headers());

        $afterFirst = $this->child->fresh()->total_stars;

        // A weaker second attempt must not reduce or add to the total.
        $this->postJson('/api/v1/sync', [
            'events' => [$this->completionEvent([$this->answer(0, true), $this->answer(1, false), $this->answer(2, false)])],
        ], $this->headers());

        $this->assertSame($afterFirst, $this->child->fresh()->total_stars);
    }

    public function test_an_event_with_a_wild_clock_is_quarantined_not_lost(): void
    {
        $event = $this->completionEvent([$this->answer(0, true)]);
        $event['client_ts'] = now()->addYears(5)->toIso8601String();

        $response = $this->postJson('/api/v1/sync', ['events' => [$event]], $this->headers());

        $response->assertOk();
        $response->assertJsonPath('rejected.0.reason', 'clock');

        $this->assertDatabaseHas('learning_events', ['id' => $event['id'], 'status' => 'quarantined']);
        $this->assertSame(0, MissionAttempt::where('child_id', $this->child->id)->count());
    }

    public function test_a_child_from_another_family_is_refused(): void
    {
        $stranger = Guardian::create([
            'name' => 'Other Parent',
            'email' => 'other@test.local',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $theirChild = $stranger->children()->create(['name' => 'Theirs', 'avatar' => 'panda']);

        $response = $this->postJson('/api/v1/sync', ['events' => []], [
            'X-Device-Id' => 'test-device',
            'X-Child-Id' => (string) $theirChild->id,
            'Accept' => 'application/json',
        ]);

        $response->assertNotFound();
        $response->assertJsonPath('error.code', 'child_not_found');
    }

    public function test_sync_requires_a_token(): void
    {
        // Drop the acting-as guardian for this one request.
        app('auth')->forgetGuards();

        $this->postJson('/api/v1/sync', ['events' => []], ['X-Device-Id' => 'test-device'])
            ->assertUnauthorized();
    }
}
