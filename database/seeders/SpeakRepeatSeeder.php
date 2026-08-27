<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuestionOption;
use App\Models\QuizType;

class SpeakRepeatSeeder extends Seeder
{
    public function run(): void
    {
        $qt = QuizType::where('slug', 'speak-repeat')->first();
        if (!$qt) {
            $this->command->error('QuizType "speak-repeat" not found!');
            return;
        }

        // -------------------------------------------------------
        // 1) Add to first lesson's quiz (Letter A — Quick Check) as Q6
        // -------------------------------------------------------
        $firstLesson = \App\Models\Lesson::where('title', 'like', '%Letter A%')->first() ?? \App\Models\Lesson::first();
        $quiz49 = $firstLesson ? Quiz::where('lesson_id', $firstLesson->id)->first() : null;
        if ($quiz49) {
            $exists = QuizQuestion::where('quiz_id', $quiz49->id)
                ->where('quiz_type_id', $qt->id)->exists();

            if (!$exists) {
                $q = QuizQuestion::create([
                    'quiz_id'      => $quiz49->id,
                    'quiz_type_id' => $qt->id,
                    'prompt'       => '🎙️ Now say the letter "A" out loud! Tap the mic and speak!',
                    'hint'         => 'Say "A" like in "Apple"!',
                    'explanation'  => 'Great speaking! The letter A says "ahh" like in Apple! 🍎',
                    'points'       => 1,
                    'sort_order'   => 99,
                    'metadata'     => json_encode([
                        'word'  => 'A',
                        'emoji' => '🍎',
                    ]),
                ]);

                QuestionOption::create([
                    'question_id' => $q->id,
                    'text_value'  => 'A',
                    'is_correct'  => true,
                    'sort_order'  => 1,
                ]);

                $this->command->info("✅ Added Speak & Repeat question to Quiz 49 (Letter A — Quick Check).");
            } else {
                $this->command->line('Speak & Repeat question already exists on Quiz 49, skipping.');
            }
        }

        // -------------------------------------------------------
        // 2) Create standalone "Animals Speak & Repeat" quiz
        // -------------------------------------------------------
        $lesson = \App\Models\Lesson::find(83); // Animal Friends lesson
        if (!$lesson) {
            $lesson = \App\Models\Lesson::orderBy('id', 'desc')->first();
        }

        $existingQuiz = Quiz::where('title', 'Speak & Repeat — Animals!')->first();
        if ($existingQuiz) {
            $this->command->line("Speak & Repeat standalone quiz already exists (ID {$existingQuiz->id}), skipping.");
            return;
        }

        $quiz = Quiz::create([
            'lesson_id'             => $lesson->id,
            'title'                 => 'Speak & Repeat — Animals!',
            'instructions'          => 'Practice saying animal names out loud!',
            'status'                => 'published',
            'pass_threshold_percent' => 60,
        ]);

        // Q1: Cat
        $q1 = QuizQuestion::create([
            'quiz_id'      => $quiz->id,
            'quiz_type_id' => $qt->id,
            'prompt'       => '🐱 Say "Cat" out loud! Tap the mic and repeat!',
            'hint'         => 'Cat starts with "C" — say it clearly!',
            'explanation'  => 'Cat says "meow"! 🐱 Great speaking!',
            'points'       => 1,
            'sort_order'   => 1,
            'metadata'     => json_encode(['word' => 'Cat', 'emoji' => '🐱']),
        ]);
        QuestionOption::create([
            'question_id' => $q1->id,
            'text_value'  => 'Cat',
            'is_correct'  => true,
            'sort_order'  => 1,
        ]);

        // Q2: Dog
        $q2 = QuizQuestion::create([
            'quiz_id'      => $quiz->id,
            'quiz_type_id' => $qt->id,
            'prompt'       => '🐶 Say "Dog" out loud! Tap the mic and repeat!',
            'hint'         => 'Dog starts with "D" — say it clearly!',
            'explanation'  => 'Dog says "woof"! 🐶 Great speaking!',
            'points'       => 1,
            'sort_order'   => 2,
            'metadata'     => json_encode(['word' => 'Dog', 'emoji' => '🐶']),
        ]);
        QuestionOption::create([
            'question_id' => $q2->id,
            'text_value'  => 'Dog',
            'is_correct'  => true,
            'sort_order'  => 1,
        ]);

        // Q3: Bird
        $q3 = QuizQuestion::create([
            'quiz_id'      => $quiz->id,
            'quiz_type_id' => $qt->id,
            'prompt'       => '🐦 Say "Bird" out loud! Tap the mic and repeat!',
            'hint'         => 'Bird starts with "B" — say it clearly!',
            'explanation'  => 'Bird says "tweet tweet"! 🐦 Great speaking!',
            'points'       => 1,
            'sort_order'   => 3,
            'metadata'     => json_encode(['word' => 'Bird', 'emoji' => '🐦']),
        ]);
        QuestionOption::create([
            'question_id' => $q3->id,
            'text_value'  => 'Bird',
            'is_correct'  => true,
            'sort_order'  => 1,
        ]);

        $this->command->info("✅ Created standalone Speak & Repeat quiz (ID {$quiz->id}) on Lesson {$lesson->id}.");
    }
}