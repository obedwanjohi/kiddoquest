<?php

namespace Database\Seeders;

use App\Models\Curriculum;
use App\Models\Level;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\SubStrand;
use App\Models\Narration;
use App\Models\Lesson;
use App\Models\LessonAsset;
use App\Models\QuestionBank;
use App\Models\QuizQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CurriculumSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Seeding CBC Curriculum structure...');

        $levels = $this->seedLevels();
        $this->assignSubjectsToLevels($levels);
        $this->seedSubStrands();
        $this->seedNarrations();
        $this->seedLessonAssets();
        $this->seedQuestionBanks();

        $this->command->info('✅ Curriculum seeding complete!');
    }

    private function seedLevels(): array
    {
        // Ensure the default CBC curriculum exists (the migration also creates it;
        // firstOrCreate keeps the seeder idempotent when run on a fresh DB).
        $curriculum = Curriculum::firstOrCreate(
            ['code' => 'CBC'],
            [
                'name' => 'CBC',
                'slug' => 'cbc',
                'description' => 'Kenya Competency-Based Curriculum',
                'color' => '#4F46E5',
                'icon' => '🎓',
                'sort_order' => 0,
                'status' => 'published',
            ]
        );
        $this->command->line("  ↳ Curriculum: {$curriculum->name}");

        $levels = [];
        $data = [
            ['name' => 'Play Group',      'code' => 'PP1', 'stage' => 'pre_primary', 'min_age' => 3, 'max_age' => 4, 'color' => '#FF6B9D', 'icon' => '🧸'],
            ['name' => 'Reception',       'code' => 'PP2', 'stage' => 'pre_primary', 'min_age' => 4, 'max_age' => 5, 'color' => '#FFB84C', 'icon' => '🎨'],
            ['name' => 'Grade 1',         'code' => 'G1',  'stage' => 'lower_primary', 'min_age' => 6, 'max_age' => 6, 'color' => '#4ECDC4', 'icon' => '🌟'],
            ['name' => 'Grade 2',         'code' => 'G2',  'stage' => 'lower_primary', 'min_age' => 7, 'max_age' => 7, 'color' => '#45B7D1', 'icon' => '🌈'],
            ['name' => 'Grade 3',         'code' => 'G3',  'stage' => 'lower_primary', 'min_age' => 8, 'max_age' => 8, 'color' => '#6C5CE7', 'icon' => '🚀'],
        ];

        foreach ($data as $row) {
            $level = Level::firstOrCreate(
                ['code' => $row['code']],
                array_merge($row, [
                    'curriculum_id' => $curriculum->id,
                    'slug' => Str::slug($row['name']),
                    'description' => "CBC {$row['name']} - {$row['stage']}",
                    'sort_order' => count($levels),
                    'status' => 'published',
                ])
            );
            $levels[$row['code']] = $level;
            $this->command->line("  ↳ Level: {$level->name}");
        }

        return $levels;
    }

    private function assignSubjectsToLevels(array $levels): void
    {
        // Assign existing subjects to Grade 1 by default (if not already assigned)
        $grade1 = $levels['G1'] ?? null;
        if (!$grade1) return;

        Subject::whereNull('level_id')->update(['level_id' => $grade1->id]);
        $this->command->line("  ↳ Assigned unlinked subjects to {$grade1->name}");
    }

    private function seedSubStrands(): void
    {
        // For each Topic (strand), create sub-strands
        $topics = Topic::all();

        foreach ($topics as $topic) {
            // Create 2-3 sub-strands per strand
            $subs = [
                ['name' => $topic->name . ': Basics',       'description' => 'Introduction to ' . $topic->name, 'sort_order' => 0],
                ['name' => $topic->name . ': Practice',     'description' => 'Practice activities for ' . $topic->name, 'sort_order' => 1],
                ['name' => $topic->name . ': Mastery',      'description' => 'Mastery level for ' . $topic->name, 'sort_order' => 2],
            ];

            foreach ($subs as $sub) {
                SubStrand::firstOrCreate(
                    [
                        'strand_id' => $topic->id,
                        'name' => $sub['name'],
                    ],
                    array_merge($sub, [
                        'slug' => Str::slug($sub['name']),
                        'status' => 'active',
                    ])
                );
            }
        }

        $this->command->line('  ↳ Sub-strands created for ' . $topics->count() . ' strands');
    }

    private function seedNarrations(): void
    {
        $lessons = Lesson::all();

        foreach ($lessons as $lesson) {
            // Intro narration
            Narration::firstOrCreate(
                [
                    'narratable_type' => Lesson::class,
                    'narratable_id' => $lesson->id,
                    'slot' => 'intro',
                ],
                [
                    'text' => "Welcome to {$lesson->title}! Let's learn something fun together!",
                    'voice_profile' => 'friendly_female',
                    'status' => 'draft',
                ]
            );

            // Summary narration
            Narration::firstOrCreate(
                [
                    'narratable_type' => Lesson::class,
                    'narratable_id' => $lesson->id,
                    'slot' => 'summary',
                ],
                [
                    'text' => "Great job! You've finished {$lesson->title}. You're a superstar!",
                    'voice_profile' => 'friendly_female',
                    'status' => 'draft',
                ]
            );
        }

        $this->command->line('  ↳ Narrations created for ' . $lessons->count() . ' lessons');
    }

    private function seedLessonAssets(): void
    {
        $lessons = Lesson::all();

        foreach ($lessons as $lesson) {
            // Ensure each lesson has at least one intro asset
            LessonAsset::firstOrCreate(
                [
                    'lesson_id' => $lesson->id,
                    'display_order' => 0,
                ],
                [
                    'type' => 'image',
                    'title' => $lesson->title . ' - Main Image',
                    'caption' => 'Illustration for ' . $lesson->title,
                    'status' => 'draft',
                ]
            );
        }

        $this->command->line('  ↳ Lesson assets created for ' . $lessons->count() . ' lessons');
    }

    private function seedQuestionBanks(): void
    {
        $lessons = Lesson::all();
        $count = 0;

        foreach ($lessons as $lesson) {
            // Create a main question bank per lesson
            $bank = QuestionBank::firstOrCreate(
                [
                    'lesson_id' => $lesson->id,
                    'name' => $lesson->title . ' - Main Bank',
                ],
                [
                    'description' => 'Primary question pool for ' . $lesson->title,
                    'pool_size' => 5,
                    'pass_threshold' => 70,
                    'max_attempts' => 3,
                    'shuffle' => true,
                    'status' => 'draft',
                    'sort_order' => 0,
                ]
            );
            $count++;

            // Link existing quiz questions to this bank
            QuizQuestion::whereHas('quiz', function ($q) use ($lesson) {
                $q->where('lesson_id', $lesson->id);
            })->whereNull('question_bank_id')->update([
                'question_bank_id' => $bank->id,
            ]);
        }

        $this->command->line("  ↳ Question banks created: {$count}");
    }
}