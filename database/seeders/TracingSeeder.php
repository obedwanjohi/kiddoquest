<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuestionOption;
use App\Models\QuizType;
use App\Models\Lesson;

class TracingSeeder extends Seeder
{
    public function run(): void
    {
        $qt = QuizType::where('slug', 'tracing')->first();
        if (!$qt) {
            $this->command->error('QuizType "tracing" not found!');
            return;
        }

        // -------------------------------------------------------
        // 1) Add to Quiz 49 (Letter A — Quick Check) as Q7
        // -------------------------------------------------------
        $firstLesson = Lesson::where('title', 'like', '%Letter A%')->first() ?? Lesson::first();
        $quiz49 = $firstLesson ? Quiz::where('lesson_id', $firstLesson->id)->first() : null;
        if ($quiz49) {
            $exists = QuizQuestion::where('quiz_id', $quiz49->id)
                ->where('quiz_type_id', $qt->id)->exists();

            if (!$exists) {
                $q = QuizQuestion::create([
                    'quiz_id'      => $quiz49->id,
                    'quiz_type_id' => $qt->id,
                    'prompt'       => '✏️ Practice tracing the letter A!',
                    'hint'         => 'Follow the dashed lines, then try on paper too!',
                    'explanation'  => 'A is for Apple! 🍎 Try writing it on your book!',
                    'points'       => 1,
                    'sort_order'   => 100,
                    'metadata'     => json_encode([
                        'character'  => 'A',
                        'traceType'  => 'Letter',
                        'slug'       => 'tracing-letter-A-upper',
                        'practice'   => true,
                    ]),
                ]);
                QuestionOption::create([
                    'question_id' => $q->id,
                    'text_value'  => 'A',
                    'is_correct'  => true,
                    'sort_order'  => 1,
                ]);
                $this->command->info("✅ Added Tracing question to Quiz 49.");
            } else {
                $this->command->line('Tracing question already exists on Quiz 49, skipping.');
            }
        }

        // -------------------------------------------------------
        // 2) Create or update standalone "Tracing Letters & Numbers" quiz
        // -------------------------------------------------------
        $lesson = Lesson::find(80) ?? Lesson::orderBy('id', 'asc')->first();

        $quiz = Quiz::where('title', 'Tracing — Letters & Numbers!')->first();
        if (!$quiz) {
            $quiz = Quiz::create([
                'lesson_id'              => $lesson->id,
                'title'                  => 'Tracing — Letters & Numbers!',
                'instructions'           => 'Practice tracing each character! Then try on paper too! 📝',
                'status'                 => 'published',
                'pass_threshold_percent' => 0,
            ]);
            $this->command->info("✅ Created Tracing quiz (ID {$quiz->id}).");
        }

        // Get all existing slugs (case-safe deduplication using JSON slug field)
        $existingSlugs = DB::table('quiz_questions')
            ->where('quiz_id', $quiz->id)
            ->where('quiz_type_id', $qt->id)
            ->get()
            ->map(function ($q) {
                $meta = json_decode($q->metadata ?? '{}', true);
                return $meta['slug'] ?? null;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $makeQ = function (string $char, string $type, string $caseLabel, string $slug) use ($quiz, $qt, &$existingSlugs) {
            if (in_array($slug, $existingSlugs)) {
                return false;
            }
            $existingSlugs[] = $slug;

            $fullType = $caseLabel ? "{$caseLabel} {$type}" : $type;
            $order = QuizQuestion::where('quiz_id', $quiz->id)->count() + 1;

            $q = QuizQuestion::create([
                'quiz_id'      => $quiz->id,
                'quiz_type_id' => $qt->id,
                'prompt'       => "✏️ Practice tracing the {$fullType} {$char}!",
                'hint'         => "Follow the dashed lines, then try on paper!",
                'explanation'  => "Great practice! Try writing {$char} in your book! 📝",
                'points'       => 1,
                'sort_order'   => $order,
                'metadata'     => json_encode([
                    'character'  => $char,
                    'traceType'  => $type,
                    'case'       => $caseLabel,
                    'slug'       => $slug,
                    'practice'   => true,
                ]),
            ]);
            QuestionOption::create([
                'question_id' => $q->id,
                'text_value'  => $char,
                'is_correct'  => true,
                'sort_order'  => 1,
            ]);
            return true;
        };

        // ═══ SEED: Numbers 0-10 ═══
        $numCount = 0;
        foreach (range(0, 10) as $num) {
            if ($makeQ((string)$num, 'Number', '', "tracing-num-{$num}")) $numCount++;
        }
        $this->command->info("✅ Seeded {$numCount} new Number tracing questions (0-10).");

        // ═══ SEED: Uppercase A-Z ═══
        $upperCount = 0;
        foreach (range('A', 'Z') as $char) {
            if ($makeQ($char, 'Letter', 'Uppercase', "tracing-letter-{$char}-upper")) $upperCount++;
        }
        $this->command->info("✅ Seeded {$upperCount} new Uppercase Letter tracing questions (A-Z).");

        // ═══ SEED: Lowercase a-z ═══
        $lowerCount = 0;
        foreach (range('a', 'z') as $char) {
            if ($makeQ($char, 'Letter', 'Lowercase', "tracing-letter-{$char}-lower")) $lowerCount++;
        }
        $this->command->info("✅ Seeded {$lowerCount} new Lowercase Letter tracing questions (a-z).");

        $total = $quiz->questions()->count();
        $this->command->info("📋 Tracing quiz now has {$total} total tracing questions.");
    }
}