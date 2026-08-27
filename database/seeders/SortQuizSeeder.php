<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizType;
use Illuminate\Database\Seeder;

/**
 * Adds a Drag & Drop Sort (QT-04) quiz to the Whispering Forest world.
 */
class SortQuizSeeder extends Seeder
{
    public function run(): void
    {
        $qt = QuizType::where('code', 'QT-04')->firstOrFail();

        // Find a Letter B lesson dynamically, fall back to 2nd lesson
        $lesson = Lesson::where('title', 'like', '%Letter B%')->first()
            ?? Lesson::skip(1)->first();

        if (!$lesson) {
            $this->command->warn('No lesson found for SortQuizSeeder, skipping.');
            return;
        }

        // Find existing quiz on this lesson with same title, skip if present
        $existing = Quiz::where('lesson_id', $lesson->id)
            ->where('title', 'Letter B — Sorting Game!')
            ->first();

        if ($existing) {
            $this->command->info("Sort quiz already exists (ID {$existing->id}), skipping.");
            return;
        }

        $quiz = Quiz::create([
            'lesson_id'            => $lesson->id,
            'title'                => 'Letter B — Sorting Game!',
            'instructions'         => 'Sort the things into the right boxes!',
            'status'               => 'published',
            'pass_threshold_percent'=> 60,
        ]);

        $this->createQuestions($quiz, $qt);

        $this->command->info("✅ Created sort quiz ID {$quiz->id} on lesson '{$lesson->title}'.");

        // Also add a sort question to Quiz 49 (Mission 1 — Letter A) for easy access
        $this->addToLetterAQuiz($qt);
    }

    private function createQuestions(Quiz $quiz, QuizType $qt): void
    {
        // Q1 — Color sort
        $q1 = QuizQuestion::create([
            'quiz_id'      => $quiz->id,
            'quiz_type_id' => $qt->id,
            'prompt'       => 'Sort by color! Red or Blue?',
            'hint'         => 'Think about the color!',
            'explanation'  => 'Red things are red, blue things are blue!',
            'points'       => 1,
            'sort_order'   => 1,
            'metadata'     => ['categories' => ['🔴 Red', '🔵 Blue']],
        ]);
        $this->addOption($q1, '🍓 Strawberry', '🔴 Red', 1);
        $this->addOption($q1, '🌊 Ocean',      '🔵 Blue', 2);
        $this->addOption($q1, '🍎 Apple',      '🔴 Red', 3);
        $this->addOption($q1, '👖 Jeans',      '🔵 Blue', 4);

        // Q2 — Size sort
        $q2 = QuizQuestion::create([
            'quiz_id'      => $quiz->id,
            'quiz_type_id' => $qt->id,
            'prompt'       => 'Sort by size! Big or Small?',
            'hint'         => 'Think about the size!',
            'explanation'  => 'Big things are big, small things are small!',
            'points'       => 1,
            'sort_order'   => 2,
            'metadata'     => ['categories' => ['🐘 Big', '🐭 Small']],
        ]);
        $this->addOption($q2, '🐘 Elephant', '🐘 Big',   1);
        $this->addOption($q2, '🐜 Ant',      '🐭 Small', 2);
        $this->addOption($q2, '🐳 Whale',    '🐘 Big',   3);
        $this->addOption($q2, '🐝 Bee',      '🐭 Small', 4);

        // Q3 — Fruit vs Vegetable
        $q3 = QuizQuestion::create([
            'quiz_id'      => $quiz->id,
            'quiz_type_id' => $qt->id,
            'prompt'       => 'Sort by type! Fruit or Vegetable?',
            'hint'         => 'Think about what it is!',
            'explanation'  => 'Fruits are sweet, veggies are not!',
            'points'       => 1,
            'sort_order'   => 3,
            'metadata'     => ['categories' => ['🍎 Fruit', '🥕 Vegetable']],
        ]);
        $this->addOption($q3, '🍎 Apple',    '🍎 Fruit',    1);
        $this->addOption($q3, '🥕 Carrot',   '🥕 Vegetable', 2);
        $this->addOption($q3, '🍌 Banana',   '🍎 Fruit',    3);
        $this->addOption($q3, '🥦 Broccoli', '🥕 Vegetable', 4);
    }

    private function addOption(QuizQuestion $q, string $text, string $matchKey, int $order): void
    {
        QuestionOption::create([
            'question_id'  => $q->id,
            'content_type' => 'text',
            'text_value'   => $text,
            'match_key'    => $matchKey,
            'is_correct'   => true,
            'sort_order'   => $order,
        ]);
    }

    /**
     * Adds a sort question to Quiz 49 (Mission 1 — "Letter A — Quick Check")
     * so the sort type is immediately playable without completing Mission 1 first.
     */
    private function addToLetterAQuiz(QuizType $qt): void
    {
        // Find first lesson's quiz dynamically (was hardcoded as ID 49)
        $firstLesson = Lesson::where('title', 'like', '%Letter A%')->first() ?? Lesson::first();
        $quiz = $firstLesson ? Quiz::where('lesson_id', $firstLesson->id)->first() : null;
        if (!$quiz) {
            $this->command->warn("No quiz found for Letter A, skipping sort addition.");
            return;
        }

        // Skip if a QT-04 question already exists on this quiz
        $exists = $quiz->questions()->where('quiz_type_id', $qt->id)->exists();
        if ($exists) {
            $this->command->info("Letter A quiz already has a sort question, skipping.");
            return;
        }

        $q = QuizQuestion::create([
            'quiz_id'      => $quiz->id,
            'quiz_type_id' => $qt->id,
            'prompt'       => 'Sort the A-words from the B-words!',
            'hint'         => 'Does it start with A or B?',
            'explanation'  => 'Apple and Ant start with A! Ball and Bear start with B!',
            'points'       => 1,
            'sort_order'   => 5,
            'metadata'     => ['categories' => ['🅰️ A-Words', '🐝 B-Words']],
        ]);

        $this->addOption($q, '🍎 Apple', '🅰️ A-Words', 1);
        $this->addOption($q, '⚽ Ball',  '🐝 B-Words',  2);
        $this->addOption($q, '🐜 Ant',   '🅰️ A-Words', 3);
        $this->addOption($q, '🐻 Bear',  '🐝 B-Words',  4);

        $this->command->info("✅ Added sort question to Quiz 49 (Letter A — Quick Check).");
    }
}
