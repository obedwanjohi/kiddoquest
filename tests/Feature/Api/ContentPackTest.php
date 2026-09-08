<?php

namespace Tests\Feature\Api;

use App\Models\AdventureWorld;
use App\Models\Curriculum;
use App\Models\Guardian;
use App\Models\Level;
use App\Models\Mission;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\QuizQuestion;
use App\Models\Subject;
use App\Services\Content\ContentPackBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Publishing a world, and what a device gets when it asks for one.
 */
class ContentPackTest extends TestCase
{
    use RefreshDatabase;

    protected AdventureWorld $world;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $curriculum = Curriculum::create(['name' => 'CBC', 'slug' => 'cbc', 'status' => 'published']);

        $level = Level::create([
            'curriculum_id' => $curriculum->id,
            'name' => 'Play Group',
            'code' => 'PG',
            'status' => 'published',
        ]);

        $subject = Subject::create([
            'level_id' => $level->id,
            'name' => 'Mathematics',
            'code' => 'MATH',
            'status' => 'published',
        ]);

        $this->world = AdventureWorld::create([
            'name' => 'Whispering Forest',
            'slug' => 'whispering-forest',
            'subject_id' => $subject->id,
            'theme_color' => '#16A34A',
            'icon' => '🌲',
            'sort_order' => 1,
        ]);

        $bank = QuestionBank::create(['name' => 'Counting', 'subject_id' => $subject->id, 'status' => 'published']);

        $question = QuizQuestion::create([
            'question_bank_id' => $bank->id,
            'type' => 'count_objects',
            'prompt' => 'How many apples?',
            'scoring_config' => ['count' => 3],
            'points' => 1,
        ]);

        QuestionOption::create(['question_id' => $question->id, 'text_value' => '3', 'is_correct' => true, 'sort_order' => 1]);
        QuestionOption::create(['question_id' => $question->id, 'text_value' => '2', 'is_correct' => false, 'sort_order' => 2]);

        Mission::create([
            'adventure_world_id' => $this->world->id,
            'question_bank_id' => $bank->id,
            'title' => 'Apple Counter',
            'slug' => 'apple-counter',
            'status' => 'published',
            'questions_per_session' => 10,
            'sort_order' => 1,
        ]);
    }

    public function test_it_publishes_a_pack_with_the_whole_question_bank(): void
    {
        $pack = app(ContentPackBuilder::class)->publish($this->world->fresh('subject.level'));

        $this->assertSame('pg-math-whispering-forest', $pack->pack_id);
        $this->assertSame(1, $pack->version);
        $this->assertSame(1, $pack->mission_count);
        $this->assertSame(1, $pack->question_count);
        $this->assertNotEmpty($pack->sha256);

        $document = json_decode(Storage::disk('public')->get($pack->json_path), true);

        $this->assertSame('PG', $document['level']);
        $this->assertSame('MATH', $document['subject']['code']);
        $this->assertTrue($document['world']['is_free'], 'the first world of a subject is free');
        $this->assertCount(1, $document['missions'][0]['questions']);
        $this->assertSame('count_objects', $document['missions'][0]['questions'][0]['type']);
    }

    public function test_it_caps_a_session_at_the_level_limit(): void
    {
        $pack = app(ContentPackBuilder::class)->publish($this->world->fresh('subject.level'));
        $document = json_decode(Storage::disk('public')->get($pack->json_path), true);

        // The mission asks for ten; Play Group is capped at six.
        $this->assertSame(6, $document['missions'][0]['questions_per_session']);
    }

    public function test_publishing_again_makes_a_new_version(): void
    {
        $builder = app(ContentPackBuilder::class);
        $world = $this->world->fresh('subject.level');

        $first = $builder->publish($world);
        $second = $builder->publish($world);

        $this->assertSame(1, $first->version);
        $this->assertSame(2, $second->version);
        $this->assertNotSame($first->json_path, $second->json_path);
    }

    public function test_the_catalog_lists_the_newest_version_for_a_level(): void
    {
        $builder = app(ContentPackBuilder::class);
        $world = $this->world->fresh('subject.level');
        $builder->publish($world);
        $builder->publish($world);

        $guardian = Guardian::create([
            'name' => 'Parent',
            'email' => 'catalog@test.local',
            'password' => 'password123',
            'is_active' => true,
        ]);

        Sanctum::actingAs($guardian, ['parent']);

        $response = $this->getJson('/api/v1/content/catalog?level=PG', ['X-Device-Id' => 'test-device']);

        $response->assertOk();
        $response->assertJsonPath('packs.0.pack_id', 'pg-math-whispering-forest');
        $response->assertJsonPath('packs.0.version', 2);
        $response->assertJsonPath('packs.0.is_free', true);
    }

    public function test_a_pack_download_needs_a_signed_in_parent(): void
    {
        app(ContentPackBuilder::class)->publish($this->world->fresh('subject.level'));

        $this->getJson('/api/v1/content/packs/pg-math-whispering-forest/v1/download')
            ->assertUnauthorized();
    }
}
