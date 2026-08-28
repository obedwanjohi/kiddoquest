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
                    'title' => 'Safari Apple Counter 🍎',
                    'display_title' => 'Count Apples 1 to 3 with Leo!',
                    'sort_order' => 1,
                    'questions' => [
                        // FORMAT B: Count & Tap Number (Questions 1, 2, 3)
                        [
                            'question' => 'How many juicy red apples do you see? Tap their number! 🍎',
                            'options' => ['1', '2', '3'],
                            'correct' => 0, // '1'
                        ],
                        [
                            'question' => 'How many juicy red apples do you see? Tap their number! 🍎🍎',
                            'options' => ['2', '1', '3'],
                            'correct' => 0, // '2'
                        ],
                        [
                            'question' => 'How many juicy red apples do you see? Tap their number! 🍎🍎🍎',
                            'options' => ['3', '2', '1'],
                            'correct' => 0, // '3'
                        ],
                        // FORMAT C: Image Card Choice (Questions 4, 5, 6)
                        [
                            'question' => 'Which picture card shows 1 red apple? Tap it!',
                            'options' => ['1 Apple 🍎', '2 Apples 🍎🍎', '3 Apples 🍎🍎🍎'],
                            'correct' => 0, // '1 Apple 🍎'
                        ],
                        [
                            'question' => 'Which picture card shows 2 red apples? Tap it!',
                            'options' => ['1 Apple 🍎', '2 Apples 🍎🍎', '3 Apples 🍎🍎🍎'],
                            'correct' => 1, // '2 Apples 🍎🍎'
                        ],
                        [
                            'question' => 'Which picture card shows 3 red apples? Tap it!',
                            'options' => ['1 Apple 🍎', '2 Apples 🍎🍎', '3 Apples 🍎🍎🍎'],
                            'correct' => 2, // '3 Apples 🍎🍎🍎'
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
            $cleanTitle = trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $mData['title']));
            $firstWord = explode(' ', $cleanTitle)[0] ?? 'Safari';
            $secondWord = explode(' ', $cleanTitle)[1] ?? 'Apple';

            $lesson = Lesson::where('title', 'ilike', "%{$firstWord}%{$secondWord}%")
                ->orWhere('title', 'ilike', "%{$secondWord}%")
                ->orWhere('slug', 'ilike', '%' . \Illuminate\Support\Str::slug($cleanTitle) . '%')
                ->first() ?? Lesson::first();

            $mission = Mission::updateOrCreate(
                [
                    'adventure_world_id' => $world->id,
                    'title' => $mData['title'],
                ],
                [
                    'lesson_id' => $lesson?->id,
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

            // Auto-link Video Media if present
            $videoMedia = \App\Models\Media::where('type', 'video')->latest()->first();

            if ($videoMedia && $mData['sort_order'] === 1 && $world->slug === 'whispering-forest') {
                $mission->video_media_id = $videoMedia->id;
            }

            $mission->question_bank_id = $bank->id;
            $mission->save();

            // Resolve uploaded image media
            $appleMedia = \App\Models\Media::where('name', 'ilike', 'apple%')->first();
            $card1Media = \App\Models\Media::where('name', 'ilike', '%card%1%')->first();
            $card2Media = \App\Models\Media::where('name', 'ilike', '%card%2%')->first();
            $card3Media = \App\Models\Media::where('name', 'ilike', '%card%3%')->first();

            // Resolve Quiz Types
            $countTypeId = \App\Models\QuizType::where('code', 'QT-09')->value('id') ?? 9;
            $mcTypeId    = \App\Models\QuizType::where('code', 'QT-01')->value('id') ?? 1;

            // Create Questions
            foreach ($mData['questions'] as $qIdx => $qData) {
                $isCountType = ($qIdx < 3);
                $quizTypeId  = $isCountType ? $countTypeId : $mcTypeId;
                $targetCount = ($qIdx % 3) + 1; // 1, 2, 3

                $question = QuizQuestion::updateOrCreate(
                    [
                        'question_bank_id' => $bank->id,
                        'prompt' => $qData['question'],
                    ],
                    [
                        'quiz_type_id' => $quizTypeId,
                        'points' => 1,
                        'sort_order' => $qIdx + 1,
                        'scoring_config' => $isCountType ? [
                            'count' => $targetCount,
                            'target_count' => $targetCount,
                            'image_url' => $appleMedia?->url,
                        ] : null,
                    ]
                );

                // Options
                foreach ($qData['options'] as $oIdx => $optText) {
                    $optImage = null;
                    if (!$isCountType) {
                        if (str_contains($optText, '1 Apple') || $oIdx === 0) {
                            $optImage = $card1Media?->url;
                        } elseif (str_contains($optText, '2 Apples') || $oIdx === 1) {
                            $optImage = $card2Media?->url;
                        } elseif (str_contains($optText, '3 Apples') || $oIdx === 2) {
                            $optImage = $card3Media?->url;
                        }
                    }

                    QuestionOption::updateOrCreate(
                        [
                            'question_id' => $question->id,
                            'sort_order' => $oIdx + 1,
                        ],
                        [
                            'text_value' => $isCountType ? $optText : '',
                            'content_type' => $optImage ? 'image' : 'text',
                            'is_correct' => ($oIdx === $qData['correct']),
                            'image_url' => $optImage,
                        ]
                    );
                }
            }
        }
    }
}
