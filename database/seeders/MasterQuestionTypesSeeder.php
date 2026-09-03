<?php

namespace Database\Seeders;

use App\Models\AdventureWorld;
use App\Models\Mission;
use App\Models\QuestionBank;
use App\Models\QuizQuestion;
use App\Models\QuestionOption;
use App\Models\QuizType;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterQuestionTypesSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Ensure Master Subject & Adventure World exist
            $subject = Subject::firstOrCreate(
                ['slug' => 'master-qa-lab'],
                [
                    'name' => 'Master QA Lab 🧪',
                    'sort_order' => 999,
                ]
            );

            $world = AdventureWorld::firstOrCreate(
                ['slug' => 'master-qa-lab-world'],
                [
                    'subject_id' => $subject->id,
                    'name' => 'Master QA Lab 🧪',
                    'display_title' => 'Master Question Types Lab 🧪',
                    'description' => 'Test mission for QT-10, QT-08, QT-03, QT-04, and QT-05 templates.',
                    'icon' => '🧪',
                    'sort_order' => 999,
                ]
            );

            // 2. Ensure Question Bank & Mission exist
            $bank = QuestionBank::firstOrCreate(
                ['name' => 'Master QA Question Bank'],
                [
                    'subject_id' => $subject->id,
                    'description' => 'QA Bank for QT-10, QT-08, QT-03, QT-04, QT-05',
                ]
            );

            $missionData = [
                'adventure_world_id' => $world->id,
                'question_bank_id'   => $bank->id,
                'title'              => 'Master Question Types Lab 🧪',
                'display_title'      => 'Master Question Types Lab 🧪',
                'description'        => 'Test all 5 interaction templates!',
                'status'             => 'published',
                'sort_order'         => 1,
                'questions_per_session' => 5,
                'pass_threshold_percent' => 60,
            ];

            if (\Illuminate\Support\Facades\Schema::hasColumn('missions', 'lesson_id')) {
                $lessonId = DB::table('lessons')->value('id') ?? 1;
                $missionData['lesson_id'] = $lessonId;
            }

            $mission = Mission::updateOrCreate(
                ['slug' => 'master-qa-types-mission'],
                $missionData
            );

            // Helper to get QuizType IDs safely without falling back to ID 1
            $getQtId = function($code, $slug) {
                $id = QuizType::where('code', $code)->orWhere('slug', $slug)->value('id');
                if (!$id) {
                    $qt = QuizType::create([
                        'code' => $code,
                        'slug' => $slug,
                        'name' => ucfirst(str_replace('-', ' ', $slug)),
                        'interaction_mode' => 'tap',
                        'has_options' => true,
                        'is_scoring_type' => true,
                    ]);
                    $id = $qt->id;
                }
                return $id;
            };

            // Purge old test questions for this bank to ensure a clean slate
            $bank->questions()->delete();

            // -------------------------------------------------------------
            // QUESTION 1: QT-10 — Complete the Pattern (🔴 🔵 🔴 🔵 ?)
            // -------------------------------------------------------------
            $q1 = QuizQuestion::create([
                'question_bank_id' => $bank->id,
                'quiz_type_id'     => $getQtId('QT-10', 'complete-pattern'),
                'type'             => 'pattern',
                'prompt'           => 'Complete the color pattern! What comes next? 🔴 🔵 🔴 🔵 ❓',
                'narration_text'   => 'Complete the color pattern! Red, Blue, Red, Blue. What comes next?',
                'sort_order'       => 1,
            ]);

            QuestionOption::create(['question_id' => $q1->id, 'text_value' => '🔴', 'is_correct' => true, 'sort_order' => 1]);
            QuestionOption::create(['question_id' => $q1->id, 'text_value' => '🔵', 'is_correct' => false, 'sort_order' => 2]);
            QuestionOption::create(['question_id' => $q1->id, 'text_value' => '🟡', 'is_correct' => false, 'sort_order' => 3]);

            // -------------------------------------------------------------
            // QUESTION 2: QT-08 — Fill the Blank / Spell (C _ T 🐱)
            // -------------------------------------------------------------
            $q2 = QuizQuestion::create([
                'question_bank_id' => $bank->id,
                'quiz_type_id'     => $getQtId('QT-08', 'fill-blank'),
                'type'             => 'fill_blank',
                'prompt'           => 'Fill in the missing letter for C _ T 🐱',
                'narration_text'   => 'Fill in the missing letter for Cat!',
                'sort_order'       => 2,
            ]);

            QuestionOption::create(['question_id' => $q2->id, 'text_value' => 'A', 'is_correct' => true, 'sort_order' => 1]);
            QuestionOption::create(['question_id' => $q2->id, 'text_value' => 'O', 'is_correct' => false, 'sort_order' => 2]);
            QuestionOption::create(['question_id' => $q2->id, 'text_value' => 'E', 'is_correct' => false, 'sort_order' => 3]);

            // -------------------------------------------------------------
            // QUESTION 3: QT-03 — Matching Pairs (Parent ➔ Baby)
            // -------------------------------------------------------------
            $q3 = QuizQuestion::create([
                'question_bank_id' => $bank->id,
                'quiz_type_id'     => $getQtId('QT-03', 'matching'),
                'type'             => 'matching',
                'prompt'           => 'Match each animal parent to its baby! 🐶 🐱 🐮',
                'narration_text'   => 'Match each animal parent to its baby!',
                'sort_order'       => 3,
            ]);

            QuestionOption::create(['question_id' => $q3->id, 'text_value' => '🐶', 'match_key' => '🐶 Puppy', 'is_correct' => true, 'sort_order' => 1]);
            QuestionOption::create(['question_id' => $q3->id, 'text_value' => '🐱', 'match_key' => '🐱 Kitten', 'is_correct' => true, 'sort_order' => 2]);
            QuestionOption::create(['question_id' => $q3->id, 'text_value' => '🐮', 'match_key' => '🐮 Calf', 'is_correct' => true, 'sort_order' => 3]);

            // -------------------------------------------------------------
            // QUESTION 4: QT-04 — Drag & Drop Sort (Farm vs Wild Animals)
            // -------------------------------------------------------------
            $q4 = QuizQuestion::create([
                'question_bank_id' => $bank->id,
                'quiz_type_id'     => $getQtId('QT-04', 'drag-sort'),
                'type'             => 'drag_sort',
                'prompt'           => 'Sort the animals into Farm Animals 🐮 vs Wild Animals 🦁!',
                'narration_text'   => 'Sort the animals into Farm Animals and Wild Animals!',
                'scoring_config'   => ['categories' => ['Farm Animals 🐮', 'Wild Animals 🦁']],
                'sort_order'       => 4,
            ]);

            QuestionOption::create(['question_id' => $q4->id, 'text_value' => '🐮', 'match_key' => 'Farm Animals 🐮', 'is_correct' => true, 'sort_order' => 1]);
            QuestionOption::create(['question_id' => $q4->id, 'text_value' => '🐷', 'match_key' => 'Farm Animals 🐮', 'is_correct' => true, 'sort_order' => 2]);
            QuestionOption::create(['question_id' => $q4->id, 'text_value' => '🦁', 'match_key' => 'Wild Animals 🦁', 'is_correct' => true, 'sort_order' => 3]);
            QuestionOption::create(['question_id' => $q4->id, 'text_value' => '🦒', 'match_key' => 'Wild Animals 🦁', 'is_correct' => true, 'sort_order' => 4]);

            // -------------------------------------------------------------
            // QUESTION 5: QT-05 — Drag & Drop Sequence (Numbers 1 to 4)
            // -------------------------------------------------------------
            $q5 = QuizQuestion::create([
                'question_bank_id' => $bank->id,
                'quiz_type_id'     => $getQtId('QT-05', 'drag-sequence'),
                'type'             => 'drag_sequence',
                'prompt'           => 'Put the numbers in order from 1 to 4! 🔢',
                'narration_text'   => 'Put the numbers in order from 1 to 4!',
                'sort_order'       => 5,
            ]);

            QuestionOption::create(['question_id' => $q5->id, 'text_value' => '1', 'is_correct' => true, 'sort_order' => 1]);
            QuestionOption::create(['question_id' => $q5->id, 'text_value' => '2', 'is_correct' => true, 'sort_order' => 2]);
            QuestionOption::create(['question_id' => $q5->id, 'text_value' => '3', 'is_correct' => true, 'sort_order' => 3]);
            QuestionOption::create(['question_id' => $q5->id, 'text_value' => '4', 'is_correct' => true, 'sort_order' => 4]);

            // Update pool count on Question Bank
            $bank->update(['pool_count' => 5]);
        });
    }
}
