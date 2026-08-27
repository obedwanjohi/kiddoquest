<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizType;
use Illuminate\Database\Seeder;

/**
 * Adds a Memory Match (QT-11) quiz.
 */
class MemoryMatchSeeder extends Seeder
{
    public function run(): void
    {
        $qt = QuizType::where('slug', 'memory-match')->first();
        if (!$qt) {
            $qt = QuizType::where('code', 'QT-11')->first();
        }
        if (!$qt) {
            $this->command->error('❌ Memory Match quiz type not found!');
            return;
        }

        // ── 1. Add question to Quiz 49 (Whispering Forest Mission 1) for easy testing ──
        $this->addToLetterAQuiz($qt);

        // ── 2. Create standalone Memory Match quiz on Lesson 83 ──
        $lesson = Lesson::find(83) ?? Lesson::first();
        if (!$lesson) {
            $this->command->error('❌ No lessons found!');
            return;
        }

        $existing = Quiz::where('lesson_id', $lesson->id)
            ->where('title', 'Animal Friends Memory Match')
            ->first();

        if ($existing) {
            $this->command->info("Memory Match quiz already exists (ID {$existing->id}), skipping standalone creation.");
            return;
        }

        $quiz = Quiz::create([
            'lesson_id'              => $lesson->id,
            'title'                  => 'Animal Friends Memory Match',
            'instructions'           => 'Flip cards to find matching pairs! Find all the pairs to win!',
            'status'                 => 'published',
            'pass_threshold_percent' => 80,
        ]);

        $this->createQuestions($quiz, $qt);

        $this->command->info("✅ Created Memory Match quiz ID {$quiz->id} on lesson '{$lesson->title}'.");
    }

    /**
     * Adds a memory match question to Quiz 49 (Mission 1 — "Letter A — Quick Check")
     * in Whispering Forest, so the memory match type is immediately playable.
     */
    private function addToLetterAQuiz(QuizType $qt): void
    {
        // Find first lesson's quiz dynamically (was hardcoded as ID 49)
        $firstLesson = Lesson::where('title', 'like', '%Letter A%')->first() ?? Lesson::first();
        $quiz = $firstLesson ? Quiz::where('lesson_id', $firstLesson->id)->first() : null;
        if (!$quiz) {
            $this->command->warn("No quiz found for Letter A, skipping memory match addition.");
            return;
        }

        // Skip if a QT-11 question already exists on this quiz
        $exists = $quiz->questions()->where('quiz_type_id', $qt->id)->exists();
        if ($exists) {
            $this->command->info("Letter A quiz already has a memory match question, skipping.");
            return;
        }

        $q = QuizQuestion::create([
            'quiz_id'      => $quiz->id,
            'quiz_type_id' => $qt->id,
            'prompt'       => '🃏 Memory Match! Flip the cards and find the letter pairs!',
            'hint'         => 'Big A goes with small a! Remember where they are! 🧠',
            'explanation'  => 'You found all the letter pairs! Amazing memory! 🎉',
            'points'       => 2,
            'sort_order'   => 6,
            'metadata'     => ['theme' => 'Letters', 'instructions' => 'Find all 3 matching pairs!'],
        ]);

        $this->addOption($q, 'A', 'a', 1);
        $this->addOption($q, 'a', 'a', 2);
        $this->addOption($q, 'B', 'b', 3);
        $this->addOption($q, 'b', 'b', 4);
        $this->addOption($q, 'C', 'c', 5);
        $this->addOption($q, 'c', 'c', 6);

        $this->command->info("✅ Added memory match question to Quiz 49 (Letter A — Quick Check).");
    }

    private function createQuestions(Quiz $quiz, QuizType $qt): void
    {
        // ─── Q1: Animal Sounds (4 pairs = 8 cards) ───
        $q1 = QuizQuestion::create([
            'quiz_id'      => $quiz->id,
            'quiz_type_id' => $qt->id,
            'prompt'       => '🃏 Flip the cards and find the matching animal pairs! Can you remember where they are?',
            'hint'         => 'Take your time! Remember where each card is! 🧠',
            'explanation'  => 'You found all the animal pairs! Amazing memory! 🎉',
            'points'       => 2,
            'sort_order'   => 1,
            'metadata'     => ['theme' => 'Animals & Sounds', 'instructions' => 'Find all 4 matching pairs!'],
        ]);
        $this->addOption($q1, '🐮 Cow',       'cow',  1);
        $this->addOption($q1, 'Moo Moo',     'cow',  2);
        $this->addOption($q1, '🐱 Cat',       'cat',  3);
        $this->addOption($q1, 'Meow',        'cat',  4);
        $this->addOption($q1, '🐶 Dog',       'dog',  5);
        $this->addOption($q1, 'Woof Woof',   'dog',  6);
        $this->addOption($q1, '🦆 Duck',      'duck', 7);
        $this->addOption($q1, 'Quack Quack', 'duck', 8);

        // ─── Q2: Upper & Lowercase Letters (3 pairs = 6 cards) ───
        $q2 = QuizQuestion::create([
            'quiz_id'      => $quiz->id,
            'quiz_type_id' => $qt->id,
            'prompt'       => '🅰️ Match the BIG letters with the small letters! Find all the pairs!',
            'hint'         => 'Big A goes with small a! 🎯',
            'explanation'  => 'You matched all the letters! You are so smart! ⭐',
            'points'       => 2,
            'sort_order'   => 2,
            'metadata'     => ['theme' => 'Letters', 'instructions' => 'Find all 3 matching pairs!'],
        ]);
        $this->addOption($q2, 'A', 'a', 1);
        $this->addOption($q2, 'a', 'a', 2);
        $this->addOption($q2, 'B', 'b', 3);
        $this->addOption($q2, 'b', 'b', 4);
        $this->addOption($q2, 'C', 'c', 5);
        $this->addOption($q2, 'c', 'c', 6);

        // ─── Q3: Colors & Fruits (4 pairs = 8 cards) ───
        $q3 = QuizQuestion::create([
            'quiz_id'      => $quiz->id,
            'quiz_type_id' => $qt->id,
            'prompt'       => '🌈 Match the colors with the right fruits! Flip and find!',
            'hint'         => '🍎 is Red! 🍌 is Yellow! What about the others?',
            'explanation'  => 'You matched all the colors! Color-rific! 🎨',
            'points'       => 2,
            'sort_order'   => 3,
            'metadata'     => ['theme' => 'Colors & Fruits', 'instructions' => 'Find all 4 matching pairs!'],
        ]);
        $this->addOption($q3, '🍎 Apple',  'red',    1);
        $this->addOption($q3, '🔴 Red',    'red',    2);
        $this->addOption($q3, '🍌 Banana', 'yellow', 3);
        $this->addOption($q3, '🟡 Yellow', 'yellow', 4);
        $this->addOption($q3, '🫐 Berries','blue',   5);
        $this->addOption($q3, '🔵 Blue',   'blue',   6);
        $this->addOption($q3, '🍇 Grapes', 'purple', 7);
        $this->addOption($q3, '🟣 Purple', 'purple', 8);
    }

    private function addOption(QuizQuestion $q, string $text, string $matchKey, int $order): void
    {
        QuestionOption::create([
            'question_id'  => $q->id,
            'content_type' => 'text',
            'text_value'   => $text,
            'match_key'    => $matchKey,
            'is_correct'   => false,
            'sort_order'   => $order,
        ]);
    }
}