<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\SubStrand;
use App\Models\QuestionBank;
use App\Models\QuizQuestion;
use App\Models\QuestionOption;
use App\Models\QuizType;

class PrePrimaryMissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get or Create Core Subjects
        $math = Subject::firstOrCreate(['slug' => 'mathematical-activities'], [
            'name' => 'Mathematical Activities',
            'code' => 'MATH_PP',
            'color' => '#3b82f6',
            'icon' => '🔢',
            'status' => 'active'
        ]);

        $english = Subject::firstOrCreate(['slug' => 'language-activities'], [
            'name' => 'Language & Phonics Activities',
            'code' => 'LANG_PP',
            'color' => '#10b981',
            'icon' => '🔤',
            'status' => 'active'
        ]);

        $cre = Subject::firstOrCreate(['slug' => 'religious-education'], [
            'name' => 'Christian Religious Education (CRE)',
            'code' => 'CRE_PP',
            'color' => '#8b5cf6',
            'icon' => '✝️',
            'status' => 'active'
        ]);

        // 2. Fetch Quiz Types
        $qtMC      = QuizType::where('slug', 'multiple_choice')->first() ?: QuizType::first();
        $qtCount   = QuizType::where('slug', 'count_objects')->first() ?: $qtMC;
        $qtMatch   = QuizType::where('slug', 'matching')->first() ?: $qtMC;
        $qtPattern = QuizType::where('slug', 'complete_pattern')->first() ?: $qtMC;
        $qtBlank   = QuizType::where('slug', 'fill_blank')->first() ?: $qtMC;
        $qtTF      = QuizType::where('slug', 'true_false')->first() ?: $qtMC;
        $qtSeq     = QuizType::where('slug', 'drag_sequence')->first() ?: $qtMC;
        $qtSort    = QuizType::where('slug', 'drag_sort')->first() ?: $qtMC;
        $qtSpeak   = QuizType::where('slug', 'speak_repeat')->first() ?: $qtMC;

        // --- MATH MISSIONS (4) ---
        $this->createMission($math, 'Safari Number Adventure (Counting 1-5)', 'Learn numbers 1 to 5 with safari animals!', [
            [
                'type' => $qtCount,
                'prompt' => 'How many juicy red apples are on the tree?',
                'scoring' => ['emoji' => '🍎', 'target_count' => 3],
                'options' => [
                    ['text' => '1', 'correct' => false],
                    ['text' => '3', 'correct' => true],
                    ['text' => '5', 'correct' => false],
                    ['text' => '2', 'correct' => false],
                ]
            ],
            [
                'type' => $qtCount,
                'prompt' => 'How many friendly giraffes do you see?',
                'scoring' => ['emoji' => '🦒', 'target_count' => 4],
                'options' => [
                    ['text' => '2', 'correct' => false],
                    ['text' => '4', 'correct' => true],
                    ['text' => '1', 'correct' => false],
                    ['text' => '5', 'correct' => false],
                ]
            ]
        ]);

        $this->createMission($math, 'Shape & Pattern Safari', 'Complete fun geometric shapes and patterns!', [
            [
                'type' => $qtPattern,
                'prompt' => 'What shape comes next in the pattern? 🔴 🟦 🔴 🟦 [ ? ]',
                'options' => [
                    ['text' => 'Red Circle 🔴', 'correct' => true],
                    ['text' => 'Blue Square 🟦', 'correct' => false],
                    ['text' => 'Green Star ⭐', 'correct' => false],
                    ['text' => 'Yellow Triangle 🔺', 'correct' => false],
                ]
            ],
            [
                'type' => $qtMatch,
                'prompt' => 'Match each shape to its name!',
                'options' => [
                    ['text' => 'Circle 🔴', 'key' => 'pair_1', 'correct' => true],
                    ['text' => 'Round Shape', 'key' => 'pair_1', 'correct' => true],
                    ['text' => 'Square 🟦', 'key' => 'pair_2', 'correct' => true],
                    ['text' => '4 Equal Sides', 'key' => 'pair_2', 'correct' => true],
                ]
            ]
        ]);

        $this->createMission($math, 'Toy Box Addition (Sums to 5)', 'Add toys together to find the total sum!', [
            [
                'type' => $qtMC,
                'prompt' => '2 Teddy Bears 🧸 + 1 Teddy Bear 🧸 = How many?',
                'options' => [
                    ['text' => '3 Bears', 'correct' => true],
                    ['text' => '4 Bears', 'correct' => false],
                    ['text' => '2 Bears', 'correct' => false],
                    ['text' => '5 Bears', 'correct' => false],
                ]
            ]
        ]);

        $this->createMission($math, 'Big & Small Safari Sorting', 'Sort big animals into the Big Bucket and small ones into the Small Bucket!', [
            [
                'type' => $qtSort,
                'prompt' => 'Sort animals into Big vs Small!',
                'scoring' => ['category_a' => 'Big Animals 🐘', 'category_b' => 'Small Bugs 🐜'],
                'options' => [
                    ['text' => 'Elephant 🐘', 'key' => 'Big Animals 🐘', 'correct' => true],
                    ['text' => 'Giraffe 🦒', 'key' => 'Big Animals 🐘', 'correct' => true],
                    ['text' => 'Ant 🐜', 'key' => 'Small Bugs 🐜', 'correct' => true],
                    ['text' => 'Bee 🐝', 'key' => 'Small Bugs 🐜', 'correct' => true],
                ]
            ]
        ]);

        // --- ENGLISH / PHONICS MISSIONS (4) ---
        $this->createMission($english, 'Phonics Sound Safari (A, B, C, D)', 'Listen and practice letter sounds A, B, C, D!', [
            [
                'type' => $qtSpeak,
                'prompt' => 'Say out loud: "Apple!"',
                'scoring' => ['target_word' => 'Apple'],
                'options' => [
                    ['text' => 'Apple 🍎', 'correct' => true]
                ]
            ],
            [
                'type' => $qtMC,
                'prompt' => 'Which letter does Banana 🍌 start with?',
                'options' => [
                    ['text' => 'Letter B', 'correct' => true],
                    ['text' => 'Letter A', 'correct' => false],
                    ['text' => 'Letter C', 'correct' => false],
                    ['text' => 'Letter D', 'correct' => false],
                ]
            ]
        ]);

        $this->createMission($english, 'Vowel Word Builder', 'Pop the missing vowel to build complete words!', [
            [
                'type' => $qtBlank,
                'prompt' => 'Complete the word: C _ T',
                'scoring' => ['blank_text' => 'C _ T', 'target_char' => 'A'],
                'options' => [
                    ['text' => 'A', 'correct' => true],
                    ['text' => 'O', 'correct' => false],
                    ['text' => 'U', 'correct' => false],
                ]
            ]
        ]);

        $this->createMission($english, 'Rhyming Words Party', 'Match words that rhyme together!', [
            [
                'type' => $qtMatch,
                'prompt' => 'Match words that sound the same!',
                'options' => [
                    ['text' => 'Cat 🐱', 'key' => 'pair_1', 'correct' => true],
                    ['text' => 'Hat 🎩', 'key' => 'pair_1', 'correct' => true],
                    ['text' => 'Sun ☀️', 'key' => 'pair_2', 'correct' => true],
                    ['text' => 'Fun 🎈', 'key' => 'pair_2', 'correct' => true],
                ]
            ]
        ]);

        $this->createMission($english, 'Story Sequence Builder', 'Put the story steps in order from first to last!', [
            [
                'type' => $qtSeq,
                'prompt' => 'Arrange the growth steps of a chick!',
                'options' => [
                    ['text' => '1. Egg 🥚', 'correct' => true],
                    ['text' => '2. Hatching 🐣', 'correct' => true],
                    ['text' => '3. Chick 🐥', 'correct' => true],
                ]
            ]
        ]);

        // --- CRE MISSIONS (4) ---
        $this->createMission($cre, "God's Beautiful Creation", 'Learn about God creating the sun, stars, trees, and animals!', [
            [
                'type' => $qtTF,
                'prompt' => 'God created the sun and stars to light up our sky!',
                'options' => [
                    ['text' => 'True ✅', 'correct' => true],
                    ['text' => 'False ❌', 'correct' => false],
                ]
            ]
        ]);

        $this->createMission($cre, 'Helping Hands & Kindness', 'Sort kind actions versus unkind actions!', [
            [
                'type' => $qtSort,
                'prompt' => 'Sort actions into Kind vs Unkind!',
                'scoring' => ['category_a' => 'Kind Actions 💖', 'category_b' => 'Unkind Actions ❌'],
                'options' => [
                    ['text' => 'Sharing toys 🧸', 'key' => 'Kind Actions 💖', 'correct' => true],
                    ['text' => 'Saying Thank You 🙏', 'key' => 'Kind Actions 💖', 'correct' => true],
                    ['text' => 'Pushing friends ✋', 'key' => 'Unkind Actions ❌', 'correct' => true],
                    ['text' => 'Shouting loudly 🗣️', 'key' => 'Unkind Actions ❌', 'correct' => true],
                ]
            ]
        ]);

        $this->createMission($cre, 'Saying Thank You & Gratitude', 'Learn polite words for every day!', [
            [
                'type' => $qtMC,
                'prompt' => 'What polite words do we say when someone gives us a gift?',
                'options' => [
                    ['text' => 'Thank You! 🙏', 'correct' => true],
                    ['text' => 'No!', 'correct' => false],
                    ['text' => 'Give me more!', 'correct' => false],
                    ['text' => 'Goodbye!', 'correct' => false],
                ]
            ]
        ]);

        $this->createMission($cre, 'Loving Our Family', 'Celebrate our parents, brothers, and sisters!', [
            [
                'type' => $qtMatch,
                'prompt' => 'Match family members to their descriptions!',
                'options' => [
                    ['text' => 'Father 👨', 'key' => 'pair_1', 'correct' => true],
                    ['text' => 'Dad who cares for us', 'key' => 'pair_1', 'correct' => true],
                    ['text' => 'Mother 👩', 'key' => 'pair_2', 'correct' => true],
                    ['text' => 'Mom who loves us', 'key' => 'pair_2', 'correct' => true],
                ]
            ]
        ]);
    }

    private function createMission(Subject $subject, string $title, string $description, array $questions): void
    {
        $subStrand = SubStrand::firstOrCreate(['name' => $title], [
            'strand_id' => null,
            'subject_id' => $subject->id,
            'code' => 'SS_' . strtoupper(substr(md5($title), 0, 6)),
        ]);

        $bank = QuestionBank::create([
            'name' => $title,
            'subject_id' => $subject->id,
            'sub_strand_id' => $subStrand->id,
            'description' => $description,
            'difficulty' => 'easy',
            'status' => 'published',
            'pool_size' => count($questions),
            'shuffle' => true,
        ]);

        foreach ($questions as $idx => $qData) {
            $question = QuizQuestion::create([
                'question_bank_id' => $bank->id,
                'quiz_type_id' => $qData['type']->id,
                'prompt' => $qData['prompt'],
                'narration_text' => $qData['prompt'],
                'points' => 10,
                'difficulty' => 'easy',
                'sort_order' => $idx + 1,
                'scoring_config' => $qData['scoring'] ?? [],
            ]);

            $bank->assignedQuestions()->attach($question->id, ['sort_order' => $idx + 1]);

            foreach ($qData['options'] as $oIdx => $opt) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'text_value' => $opt['text'],
                    'match_key' => $opt['key'] ?? null,
                    'is_correct' => $opt['correct'] ?? false,
                    'sort_order' => $oIdx + 1,
                ]);
            }
        }
    }
}
