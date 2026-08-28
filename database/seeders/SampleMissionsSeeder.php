<?php

namespace Database\Seeders;

use App\Models\AdventureWorld;
use App\Models\Lesson;
use App\Models\Mission;
use App\Models\QuestionBank;
use App\Models\QuizQuestion;
use App\Models\QuestionOption;
use Illuminate\Database\Seeder;

class SampleMissionsSeeder extends Seeder
{
    public function run(): void
    {
        $worlds = AdventureWorld::orderBy('sort_order')->get();
        if ($worlds->isEmpty()) {
            return;
        }

        $forestWorld = $worlds->firstWhere('slug', 'whispering-forest') ?? $worlds->get(0);
        $safariWorld = $worlds->firstWhere('slug', 'safari-plains') ?? $worlds->get(1);
        $oceanWorld  = $worlds->firstWhere('slug', 'ocean-cove') ?? $worlds->get(2);

        // ══════════════════════════════════════════════════════
        // WORLD 1: WHISPERING FOREST (MATHEMATICS 🔢)
        // ══════════════════════════════════════════════════════
        if ($forestWorld) {
            $this->seedWorldMissions($forestWorld, [
                [
                    'title' => 'Counting Apples 🍎',
                    'display_title' => 'Count 1 to 5 with Leo!',
                    'sort_order' => 1,
                    'questions' => [
                        [
                            'question' => 'How many red apples are on the tree? 🍎🍎🍎',
                            'options' => ['3', '2', '5', '4'],
                            'correct' => 0,
                        ],
                        [
                            'question' => 'Count the friendly frogs: 🐸🐸',
                            'options' => ['1', '2', '3', '4'],
                            'correct' => 1,
                        ],
                        [
                            'question' => 'Which number comes after 3?',
                            'options' => ['2', '5', '4', '1'],
                            'correct' => 2,
                        ],
                    ]
                ],
                [
                    'title' => 'Number Match 🔢',
                    'display_title' => 'Match Numbers & Animals!',
                    'sort_order' => 2,
                    'questions' => [
                        [
                            'question' => 'How many stars do you see? ⭐⭐⭐⭐',
                            'options' => ['4', '3', '2', '5'],
                            'correct' => 0,
                        ],
                        [
                            'question' => 'Count the little birds: 🐦🐦🐦',
                            'options' => ['5', '3', '2', '1'],
                            'correct' => 1,
                        ],
                    ]
                ],
                [
                    'title' => 'Shape Detective 🔍',
                    'display_title' => 'Find the Shapes in Nature',
                    'sort_order' => 3,
                    'questions' => [
                        [
                            'question' => 'Which shape is like the golden sun? ☀️',
                            'options' => ['Circle ⭕', 'Square ⏹️', 'Triangle 🔺'],
                            'correct' => 0,
                        ],
                    ]
                ],
            ]);
        }

        // ══════════════════════════════════════════════════════
        // WORLD 2: SAFARI PLAINS (LANGUAGE & PHONICS 📖)
        // ══════════════════════════════════════════════════════
        if ($safariWorld) {
            $this->seedWorldMissions($safariWorld, [
                [
                    'title' => 'Letter A Safari 🅰️',
                    'display_title' => 'Learn the "A" Sound with Leo!',
                    'sort_order' => 1,
                    'questions' => [
                        [
                            'question' => 'Which word starts with the /a/ sound?',
                            'options' => ['Apple 🍎', 'Banana 🍌', 'Car 🚗'],
                            'correct' => 0,
                        ],
                        [
                            'question' => 'Find the capital letter A:',
                            'options' => ['B', 'A', 'D', 'C'],
                            'correct' => 1,
                        ],
                    ]
                ],
                [
                    'title' => 'Letter B Bouncing Ball ⚽',
                    'display_title' => 'Discover the "B" Sound!',
                    'sort_order' => 2,
                    'questions' => [
                        [
                            'question' => 'Which animal makes the /b/ sound?',
                            'options' => ['Bear 🐻', 'Lion 🦁', 'Elephant 🐘'],
                            'correct' => 0,
                        ],
                    ]
                ],
            ]);
        }

        // ══════════════════════════════════════════════════════
        // WORLD 3: OCEAN COVE (CRE & VALUES VILLAGE ✝️)
        // ══════════════════════════════════════════════════════
        if ($oceanWorld) {
            $this->seedWorldMissions($oceanWorld, [
                [
                    'title' => "God's Beautiful Creation 🌍",
                    'display_title' => 'Discover Plants, Birds & Family',
                    'sort_order' => 1,
                    'questions' => [
                        [
                            'question' => 'Who created the beautiful sun, moon, and stars? ☀️🌙⭐',
                            'options' => ['God 💖', 'Computers 💻', 'Robots 🤖'],
                            'correct' => 0,
                        ],
                        [
                            'question' => 'How can we take care of plants and pets? 🐶🌱',
                            'options' => ['Water them and show love ❤️', 'Ignore them', 'Throw stones'],
                            'correct' => 0,
                        ],
                    ]
                ],
                [
                    'title' => 'Sharing & Kindness 💖',
                    'display_title' => 'Kindness at Home & School',
                    'sort_order' => 2,
                    'questions' => [
                        [
                            'question' => 'What is the right thing to do when playing with toys? 🧸',
                            'options' => ['Share with siblings and friends 🤝', 'Keep all toys alone', 'Hide the toys'],
                            'correct' => 0,
                        ],
                    ]
                ],
            ]);
        }
    }

    private function seedWorldMissions(AdventureWorld $world, array $missionsData): void
    {
        foreach ($missionsData as $mData) {
            $mission = Mission::updateOrCreate(
                [
                    'adventure_world_id' => $world->id,
                    'title' => $mData['title'],
                ],
                [
                    'display_title' => $mData['display_title'],
                    'slug' => \Illuminate\Support\Str::slug($mData['title']) . '-' . $world->id,
                    'description' => $mData['display_title'],
                    'pass_threshold_percent' => 60,
                    'stars_reward' => 3,
                    'questions_per_session' => count($mData['questions']),
                    'randomize_questions' => false,
                    'estimated_minutes' => 3,
                    'status' => 'published',
                    'sort_order' => $mData['sort_order'],
                ]
            );

            // Create Question Bank for Mission
            $bank = QuestionBank::updateOrCreate(
                ['name' => 'Bank - ' . $mData['title']],
                [
                    'description' => 'Questions for ' . $mData['title'],
                    'difficulty' => 'easy',
                    'status' => 'published',
                ]
            );

            $mission->question_bank_id = $bank->id;
            $mission->save();

            // Create Questions
            foreach ($mData['questions'] as $qIdx => $qData) {
                $question = QuizQuestion::updateOrCreate(
                    [
                        'question_bank_id' => $bank->id,
                        'question' => $qData['question'],
                    ],
                    [
                        'question_type' => 'multiple_choice',
                        'sort_order' => $qIdx + 1,
                    ]
                );

                // Options
                foreach ($qData['options'] as $oIdx => $optText) {
                    QuestionOption::updateOrCreate(
                        [
                            'question_id' => $question->id,
                            'option_text' => $optText,
                        ],
                        [
                            'is_correct' => ($oIdx === $qData['correct']),
                            'sort_order' => $oIdx + 1,
                        ]
                    );
                }
            }
        }
    }
}
