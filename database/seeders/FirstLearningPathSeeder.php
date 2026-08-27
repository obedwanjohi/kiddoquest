<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lesson;
use App\Models\AdventureWorld;
use App\Models\Mission;
use App\Models\QuestionBank;
use Illuminate\Support\Str;

class FirstLearningPathSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get the "Counting" lesson
        $lesson = Lesson::where('title', 'like', '%count%')->first();
        if (!$lesson) {
            $this->command->error('Counting lesson not found!');
            return;
        }

        // 2. Get the Adventure World (ID 1)
        $world = AdventureWorld::first();
        if (!$world) {
            $this->command->error('No Adventure World found!');
            return;
        }

        // 3. Clear out existing missions and question banks for this lesson to start fresh
        $existingMissions = Mission::where('lesson_id', $lesson->id)->get();
        foreach ($existingMissions as $m) {
            if ($m->question_bank_id) {
                QuestionBank::where('id', $m->question_bank_id)->delete();
            }
            $m->delete();
        }

        // 4. Define the 11 testing-focused missions
        $missionsData = [
            [
                'title' => 'Count 1-3',
                'kid_display_title' => "Count 1, 2, 3!",
                'bank_name' => 'M1 - Multiple Choice Bank',
            ],
            [
                'title' => 'Count 4-5',
                'kid_display_title' => "True or False?",
                'bank_name' => 'M2 - True/False Bank',
            ],
            [
                'title' => 'Count 6-7',
                'kid_display_title' => "Match the Numbers!",
                'bank_name' => 'M3 - Matching Bank',
            ],
            [
                'title' => 'Count 8-10',
                'kid_display_title' => "Sort the Numbers!",
                'bank_name' => 'M4 - Drag & Sort Bank',
            ],
            [
                'title' => 'Find Numbers',
                'kid_display_title' => "Drag in Order!",
                'bank_name' => 'M5 - Drag Sequence Bank',
            ],
            [
                'title' => 'Listen to Numbers',
                'kid_display_title' => "Listen & Choose!",
                'bank_name' => 'M6 - Listen & Choose Bank',
            ],
            [
                'title' => 'Say the Number',
                'kid_display_title' => "Say It Loud!",
                'bank_name' => 'M7 - Speak & Repeat Bank',
            ],
            [
                'title' => 'Missing Number',
                'kid_display_title' => "Fill in the Blank!",
                'bank_name' => 'M8 - Spell & Fill Bank',
            ],
            [
                'title' => 'Count Objects',
                'kid_display_title' => "Count the Objects!",
                'bank_name' => 'M9 - Count Objects Bank',
            ],
            [
                'title' => 'Complete the Pattern',
                'kid_display_title' => "What comes next?",
                'bank_name' => 'M10 - Complete Pattern Bank',
            ],
            [
                'title' => 'Number Champion',
                'kid_display_title' => "Number Champion!",
                'bank_name' => 'M11 - Mixed Review Bank',
            ],
        ];

        // 5. Create them
        $sortOrder = 1;
        foreach ($missionsData as $data) {
            $bank = QuestionBank::create([
                'name' => $data['bank_name'],
                'description' => "Question bank testing primarily for {$data['title']}",
                'status' => 'published',
            ]);

            Mission::create([
                'title' => $data['title'],
                'display_title' => $data['kid_display_title'],
                'lesson_id' => $lesson->id,
                'adventure_world_id' => $world->id,
                'question_bank_id' => $bank->id,
                'status' => 'published',
                'questions_per_session' => 5,
                'randomize_questions' => true,
                'pass_threshold_percent' => 60,
                'stars_reward' => 3,
                'estimated_minutes' => 5,
                'sort_order' => $sortOrder,
            ]);

            $sortOrder++;
        }

        $this->command->info('11 Testing-Focused Missions seeded successfully into the Counting lesson!');
    }
}
