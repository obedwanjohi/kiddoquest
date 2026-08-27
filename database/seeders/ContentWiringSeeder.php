<?php

namespace Database\Seeders;

use App\Models\AdventureWorld;
use App\Models\Lesson;
use App\Models\Narration;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizType;
use App\Models\QuestionOption;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class ContentWiringSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎬 Content Wiring Seeder — Building the Golden Lesson...');

        // ── 1. Find or create a topic ────────────────────────────
        $topic = Topic::firstOrCreate(
            ['name' => 'Counting Fun'],
            [
                'subject_id' => 1,
                'slug' => 'counting-fun',
                'description' => 'Learn to count from 1 to 10!',
            ]
        );

        // ── 2. Find or create the Golden Lesson ──────────────────
        $lesson = Lesson::firstOrCreate(
            ['slug' => 'golden-lesson-counting-1-to-5'],
            [
                'topic_id' => $topic->id,
                'title' => 'Counting 1 to 5 with Leo!',
                'summary' => 'Join Leo the Lion as we learn to count from 1 to 5 using fun animals and colorful objects!',
                'content' => $this->getGoldenLessonContent(),
                'content_type' => 'text',
                'duration_minutes' => 5,
                'status' => 'published',
                'published_at' => now(),
                'sort_order' => 1,
            ]
        );

        // ── 3. Create / update intro narration ───────────────────
        $introNarration = Narration::firstOrCreate(
            [
                'narratable_type' => Lesson::class,
                'narratable_id' => $lesson->id,
                'slot' => 'intro',
            ],
            [
                'text' => "Hi there! I'm Leo the Lion! Today we're going on a counting adventure! We'll learn to count from 1 to 5. Are you ready? Let's go!",
                'language' => 'en',
                'voice_profile' => 'friendly-female',
                'status' => 'published',
                // audio_path left null — kid view falls back to TTS
            ]
        );

        // ── 4. Create / update summary narration ─────────────────
        $summaryNarration = Narration::firstOrCreate(
            [
                'narratable_type' => Lesson::class,
                'narratable_id' => $lesson->id,
                'slot' => 'summary',
            ],
            [
                'text' => "Wow! You did an amazing job! Now you can count from 1 to 5! Remember: one, two, three, four, five! Keep practicing and you'll be a counting superstar!",
                'language' => 'en',
                'voice_profile' => 'friendly-female',
                'status' => 'published',
            ]
        );

        // ── 5. Wire narrations to lesson ─────────────────────────
        $lesson->update([
            'intro_narration_id' => $introNarration->id,
            'summary_narration_id' => $summaryNarration->id,
        ]);

        // ── 6. Create / update the quiz ──────────────────────────
        // quizzes table uses title + lesson_id (NOT slug); uses instructions (NOT description)
        $quiz = Quiz::firstOrCreate(
            [
                'lesson_id' => $lesson->id,
                'title' => 'Counting Challenge!',
            ],
            [
                'instructions' => 'Test your counting skills!',
                'status' => 'published',
                'sort_order' => 1,
            ]
        );

        // ── 7. Create questions ──────────────────────────────────
        $this->createQuestions($quiz);

        // ── 8. Wire to adventure world ───────────────────────────
        $this->wireToWorld($lesson);

        $this->command->info("✅ Golden Lesson wired successfully!");
        $this->command->info("   📖 Lesson: {$lesson->title} (ID: {$lesson->id})");
        $this->command->info("   🎯 Quiz: {$quiz->title} (ID: {$quiz->id})");
        $this->command->info("   🔊 Intro Narration: ID {$introNarration->id}");
        $this->command->info("   🔊 Summary Narration: ID {$summaryNarration->id}");
        $this->command->info("   ❓ Questions: " . $quiz->questions()->count());
    }

    private function createQuestions(Quiz $quiz): void
    {
        // Clear existing questions for idempotency
        $quiz->questions()->delete();

        $mcType = QuizType::where('slug', 'multiple-choice')->first();
        $countType = QuizType::where('slug', 'count-objects')->first();

        // ── Q1: Multiple Choice — "How many lions?" ──────────────
        $q1 = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'quiz_type_id' => $mcType?->id,
            'prompt' => 'How many lions do you see?',
            'prompt_image_url' => '🦁🦁🦁',
            'hint' => 'Count them one by one!',
            'explanation' => 'There are 3 lions!',
            'points' => 1,
            'sort_order' => 1,
            'metadata' => ['audio_text' => 'How many lions do you see? Count them: one, two, three!'],
        ]);

        QuestionOption::insert([
            ['question_id' => $q1->id, 'text_value' => '2', 'is_correct' => false, 'sort_order' => 1],
            ['question_id' => $q1->id, 'text_value' => '3', 'is_correct' => true, 'sort_order' => 2],
            ['question_id' => $q1->id, 'text_value' => '4', 'is_correct' => false, 'sort_order' => 3],
        ]);

        // ── Q2: Count Objects — "Count the apples!" ──────────────
        $q2 = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'quiz_type_id' => $countType?->id,
            'prompt' => 'Count the apples! How many are there?',
            'hint' => 'Point at each apple as you count!',
            'explanation' => 'There are 5 apples!',
            'points' => 1,
            'sort_order' => 2,
            'metadata' => ['objects' => ['🍎', '🍎', '🍎', '🍎', '🍎']],
        ]);

        QuestionOption::insert([
            ['question_id' => $q2->id, 'text_value' => '4', 'is_correct' => false, 'sort_order' => 1],
            ['question_id' => $q2->id, 'text_value' => '5', 'is_correct' => true, 'sort_order' => 2],
            ['question_id' => $q2->id, 'text_value' => '6', 'is_correct' => false, 'sort_order' => 3],
        ]);

        // ── Q3: Multiple Choice — "What comes after 3?" ──────────
        $q3 = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'quiz_type_id' => $mcType?->id,
            'prompt' => 'What number comes after 3?',
            'hint' => 'Count up: 1, 2, 3, ...?',
            'explanation' => '4 comes after 3! 1, 2, 3, 4!',
            'points' => 1,
            'sort_order' => 3,
        ]);

        QuestionOption::insert([
            ['question_id' => $q3->id, 'text_value' => '2', 'is_correct' => false, 'sort_order' => 1],
            ['question_id' => $q3->id, 'text_value' => '4', 'is_correct' => true, 'sort_order' => 2],
            ['question_id' => $q3->id, 'text_value' => '5', 'is_correct' => false, 'sort_order' => 3],
        ]);
    }

    private function wireToWorld(Lesson $lesson): void
    {
        $world = AdventureWorld::first();
        if (!$world) {
            $this->command->warn('   ⚠️ No AdventureWorld found — skipping world wiring.');
            return;
        }

        $exists = \DB::table('world_lessons')
            ->where('adventure_world_id', $world->id)
            ->where('lesson_id', $lesson->id)
            ->exists();

        if (!$exists) {
            \DB::table('world_lessons')->insert([
                'adventure_world_id' => $world->id,
                'lesson_id' => $lesson->id,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("   🌍 Linked to world: {$world->name}");
        }
    }

    private function getGoldenLessonContent(): string
    {
        return <<<'HTML'
<h1>Counting 1 to 5 with Leo!</h1>

<p>Hi friend! Today, Leo the Lion is going to teach us how to count from <strong>1</strong> to <strong>5</strong>!</p>

<h2>Let's Count Together!</h2>

<p><strong>One</strong> (1) — Look! There's <strong>1</strong> elephant!</p>
<p><strong>Two</strong> (2) — Now there are <strong>2</strong> butterflies!</p>
<p><strong>Three</strong> (3) — Count the lions: <strong>1, 2, 3</strong> lions!</p>
<p><strong>Four</strong> (4) — <strong>4</strong> shiny stars!</p>
<p><strong>Five</strong> (5) — <strong>5</strong> juicy apples!</p>

<h2>Practice Time!</h2>

<p>Can you count your fingers? <strong>1, 2, 3, 4, 5</strong>!</p>
<p>Can you count your toes? <strong>1, 2, 3, 4, 5</strong>!</p>

<h2>You Did It!</h2>

<p>Now you can count from 1 to 5! You're amazing! Let's play a fun game to practice!</p>
HTML;
    }
}