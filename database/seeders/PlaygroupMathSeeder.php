<?php

namespace Database\Seeders;

use App\Models\AdventureWorld;
use App\Models\Lesson;
use App\Models\Media;
use App\Models\Mission;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\QuizQuestion;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlaygroupMathSeeder extends Seeder
{
    public function run(): void
    {
        $mathSubject = Subject::where('slug', 'like', 'mathematics%')->first()
            ?? Subject::firstOrCreate(['slug' => 'mathematics-pg'], ['name' => 'Mathematics Activities', 'code' => 'MATH']);

        // 1. Ensure 3 Mathematics Worlds Exist
        $forestWorld = AdventureWorld::updateOrCreate(
            ['slug' => 'whispering-forest'],
            [
                'name' => 'Whispering Forest',
                'description' => 'Counting Numbers 1 to 3 with friendly apples, kittens, and shiny stars!',
                'icon' => '🌲',
                'theme_color' => '#10B981',
                'subject_id' => $mathSubject->id,
                'sort_order' => 1,
                'is_locked' => false,
            ]
        );

        $meadowWorld = AdventureWorld::updateOrCreate(
            ['slug' => 'sunny-meadow'],
            [
                'name' => 'Sunny Meadow',
                'description' => 'Counting Numbers 1 to 4 with backpacks, toy boxes, and zooming cars!',
                'icon' => '🎒',
                'theme_color' => '#F59E0B',
                'subject_id' => $mathSubject->id,
                'sort_order' => 2,
                'is_locked' => false,
            ]
        );

        $cookieWorld = AdventureWorld::updateOrCreate(
            ['slug' => 'cookie-trail'],
            [
                'name' => 'Yummy Cookie Trail',
                'description' => 'Counting Numbers 1 to 5 with sweet cookies, fruit baskets, and market friends!',
                'icon' => '🍪',
                'theme_color' => '#E11D48',
                'subject_id' => $mathSubject->id,
                'sort_order' => 3,
                'is_locked' => false,
            ]
        );

        // 2. Define the 20 Mathematics Missions
        $missionsData = [
            // ── WORLD 1: Whispering Forest (Counting 1 to 3) ──
            [
                'world' => $forestWorld,
                'num' => 1,
                'title' => 'Safari Apple Counter 🍎',
                'item_singular' => 'apple',
                'item_plural' => 'apples',
                'item_name' => 'juicy red apple',
                'item_names' => 'juicy red apples',
                'item_emoji' => '🍎',
                'prompt' => 'How many juicy red apples do you see? Tap their number!',
                'audio_prompt_key' => 'apple_count',
                'max_count' => 3,
            ],
            [
                'world' => $forestWorld,
                'num' => 2,
                'title' => 'Yellow Banana Counter 🍌',
                'item_singular' => 'banana',
                'item_plural' => 'bananas',
                'item_name' => 'sweet yellow banana',
                'item_names' => 'sweet yellow bananas',
                'item_emoji' => '🍌',
                'prompt' => 'How many sweet yellow bananas do you see? Tap their number!',
                'audio_prompt_key' => 'banana_count',
                'max_count' => 3,
            ],
            [
                'world' => $forestWorld,
                'num' => 3,
                'title' => 'Speedy Car Counter 🚗',
                'item_singular' => 'car',
                'item_plural' => 'cars',
                'item_name' => 'speedy little car',
                'item_names' => 'speedy little cars',
                'item_emoji' => '🚗',
                'prompt' => 'How many speedy little cars do you see? Tap their number!',
                'audio_prompt_key' => 'car_count',
                'max_count' => 3,
            ],
            [
                'world' => $forestWorld,
                'num' => 4,
                'title' => 'Playful Cat Counter 🐱',
                'item_singular' => 'cat',
                'item_plural' => 'cats',
                'item_name' => 'playful little cat',
                'item_names' => 'playful little cats',
                'item_emoji' => '🐱',
                'prompt' => 'How many playful little cats do you see? Tap their number!',
                'audio_prompt_key' => 'cat_count',
                'max_count' => 3,
            ],
            [
                'world' => $forestWorld,
                'num' => 5,
                'title' => 'Glowing Star Counter ⭐',
                'item_singular' => 'star',
                'item_plural' => 'stars',
                'item_name' => 'shining glowing star',
                'item_names' => 'shining glowing stars',
                'item_emoji' => '⭐',
                'prompt' => 'How many shining glowing stars do you see? Tap their number!',
                'audio_prompt_key' => 'star_count',
                'max_count' => 3,
            ],
            [
                'world' => $forestWorld,
                'num' => 6,
                'title' => 'Floating Balloon Counter 🎈',
                'item_singular' => 'balloon',
                'item_plural' => 'balloons',
                'item_name' => 'bright floating balloon',
                'item_names' => 'bright floating balloons',
                'item_emoji' => '🎈',
                'prompt' => 'How many bright floating balloons do you see? Tap their number!',
                'audio_prompt_key' => 'balloon_count',
                'max_count' => 3,
            ],
            [
                'world' => $forestWorld,
                'num' => 7,
                'title' => 'Chirping Bird Counter 🐦',
                'item_singular' => 'bird',
                'item_plural' => 'birds',
                'item_name' => 'chirping little bird',
                'item_names' => 'chirping little birds',
                'item_emoji' => '🐦',
                'prompt' => 'How many chirping little birds do you see? Tap their number!',
                'audio_prompt_key' => 'bird_count',
                'max_count' => 3,
            ],
            [
                'world' => $forestWorld,
                'num' => 8,
                'title' => 'Frosted Cake Counter 🍰',
                'item_singular' => 'cake',
                'item_plural' => 'cakes',
                'item_name' => 'sweet frosted cake',
                'item_names' => 'sweet frosted cakes',
                'item_emoji' => '🍰',
                'prompt' => 'How many sweet frosted cakes do you see? Tap their number!',
                'audio_prompt_key' => 'cake_count',
                'max_count' => 3,
            ],
            [
                'world' => $forestWorld,
                'num' => 9,
                'title' => 'Playful Animal Counter 🐮',
                'item_singular' => 'animal',
                'item_plural' => 'animals',
                'item_name' => 'playful little animal',
                'item_names' => 'playful little animals',
                'item_emoji' => '🐮',
                'prompt' => 'How many playful little animals do you see? Tap their number!',
                'audio_prompt_key' => 'animal_count',
                'max_count' => 3,
            ],
            [
                'world' => $forestWorld,
                'num' => 10,
                'title' => 'Mixed Fruit Counter 🧺',
                'item_singular' => 'fruit',
                'item_plural' => 'fruits',
                'item_name' => 'fresh mixed fruit',
                'item_names' => 'fresh mixed fruits',
                'item_emoji' => '🧺',
                'prompt' => 'How many fresh mixed fruits do you see? Tap their number!',
                'audio_prompt_key' => 'fruit_count',
                'max_count' => 3,
            ],

            // ── WORLD 2: Sunny Meadow (Counting 1 to 4) ──
            [
                'world' => $meadowWorld,
                'num' => 11,
                'title' => 'School Bag Counter 🎒',
                'item_singular' => 'bag',
                'item_plural' => 'bags',
                'item_name' => 'bright colorful school bag',
                'item_names' => 'bright colorful school bags',
                'item_emoji' => '🎒',
                'prompt' => 'How many bright colorful school bags do you see? Tap their number!',
                'audio_prompt_key' => 'bag_count',
                'max_count' => 4,
            ],
            [
                'world' => $meadowWorld,
                'num' => 12,
                'title' => 'Brown Bear Counter 🧸',
                'item_singular' => 'bear',
                'item_plural' => 'bears',
                'item_name' => 'cuddly brown bear',
                'item_names' => 'cuddly brown bears',
                'item_emoji' => '🧸',
                'prompt' => 'How many cuddly brown bears do you see? Tap their number!',
                'audio_prompt_key' => 'bear_count',
                'max_count' => 4,
            ],
            [
                'world' => $meadowWorld,
                'num' => 13,
                'title' => 'Chirping Bird Counter (1-4) 🐦',
                'item_singular' => 'bird',
                'item_plural' => 'birds',
                'item_name' => 'chirping little bird',
                'item_names' => 'chirping little birds',
                'item_emoji' => '🐦',
                'prompt' => 'How many chirping little birds do you see? Tap their number!',
                'audio_prompt_key' => 'bird_count',
                'max_count' => 4,
            ],
            [
                'world' => $meadowWorld,
                'num' => 14,
                'title' => 'Speedy Car Counter (1-4) 🚗',
                'item_singular' => 'car',
                'item_plural' => 'cars',
                'item_name' => 'speedy little car',
                'item_names' => 'speedy little cars',
                'item_emoji' => '🚗',
                'prompt' => 'How many speedy little cars do you see? Tap their number!',
                'audio_prompt_key' => 'car_count',
                'max_count' => 4,
            ],
            [
                'world' => $meadowWorld,
                'num' => 15,
                'title' => 'Green Tree Counter 🌲',
                'item_singular' => 'tree',
                'item_plural' => 'trees',
                'item_name' => 'tall green tree',
                'item_names' => 'tall green trees',
                'item_emoji' => '🌲',
                'prompt' => 'How many tall green trees do you see? Tap their number!',
                'audio_prompt_key' => 'tree_count',
                'max_count' => 4,
            ],

            // ── WORLD 3: Yummy Cookie Trail (Counting 1 to 5) ──
            [
                'world' => $cookieWorld,
                'num' => 16,
                'title' => 'Crunchy Cookie Counter 🍪',
                'item_singular' => 'cookie',
                'item_plural' => 'cookies',
                'item_name' => 'sweet crunchy cookie',
                'item_names' => 'sweet crunchy cookies',
                'item_emoji' => '🍪',
                'prompt' => 'How many sweet crunchy cookies do you see? Tap their number!',
                'audio_prompt_key' => 'cookie_count',
                'max_count' => 5,
            ],
            [
                'world' => $cookieWorld,
                'num' => 17,
                'title' => 'Fluffy Rabbit Counter 🐰',
                'item_singular' => 'rabbit',
                'item_plural' => 'rabbits',
                'item_name' => 'fluffy little rabbit',
                'item_names' => 'fluffy little rabbits',
                'item_emoji' => '🐰',
                'prompt' => 'How many fluffy little rabbits do you see? Tap their number!',
                'audio_prompt_key' => 'rabbit_count',
                'max_count' => 5,
            ],
            [
                'world' => $cookieWorld,
                'num' => 18,
                'title' => 'Swimming Fish Counter 🐠',
                'item_singular' => 'fish',
                'item_plural' => 'fish',
                'item_name' => 'swimming little fish',
                'item_names' => 'swimming little fish',
                'item_emoji' => '🐠',
                'prompt' => 'How many swimming little fish do you see? Tap their number!',
                'audio_prompt_key' => 'fish_count',
                'max_count' => 5,
            ],
            [
                'world' => $cookieWorld,
                'num' => 19,
                'title' => 'Striped Zebra Counter 🦓',
                'item_singular' => 'zebra',
                'item_plural' => 'zebras',
                'item_name' => 'striped little zebra',
                'item_names' => 'striped little zebras',
                'item_emoji' => '🦓',
                'prompt' => 'How many striped little zebras do you see? Tap their number!',
                'audio_prompt_key' => 'zebra_count',
                'max_count' => 5,
            ],
            [
                'world' => $cookieWorld,
                'num' => 20,
                'title' => 'Grand Apple Counter 🍎',
                'item_singular' => 'apple',
                'item_plural' => 'apples',
                'item_name' => 'juicy red apple',
                'item_names' => 'juicy red apples',
                'item_emoji' => '🍎',
                'prompt' => 'How many juicy red apples do you see? Tap their number!',
                'audio_prompt_key' => 'apple_count',
                'max_count' => 5,
            ],
        ];

        // 3. Process Each Mission and Wire Media
        foreach ($missionsData as $mData) {
            $mNum = $mData['num'];
            $sing = $mData['item_singular'];
            $plur = $mData['item_plural'];
            $maxC = $mData['max_count'];

            // Find uploaded video for this mission (e.g. math_m1_video.mp4)
            $videoUrl = $this->findMediaUrl(["math_m{$mNum}_video", "m{$mNum}_video"]);

            // Find counting prompt voiceover audio (e.g. apple_count.mp3)
            $promptAudioUrl = $this->findMediaUrl([$mData['audio_prompt_key'], "count_{$sing}"]);

            // Find single item image (e.g. 1_apple.jpg)
            $singleItemImgUrl = $this->findMediaUrl(["1_{$sing}", "obj_{$sing}"]);

            // Create Question Bank
            $qBank = QuestionBank::updateOrCreate(
                ['name' => "Question Bank — {$mData['title']}"],
                ['subject_id' => $mathSubject->id, 'description' => "Questions for {$mData['title']}"]
            );

            // Find or create Lesson for this mission
            $lesson = Lesson::firstOrCreate(
                ['slug' => Str::slug($mData['title'])],
                [
                    'title' => $mData['title'],
                    'description' => "Counting lesson for {$mData['title']}",
                    'sort_order' => $mNum,
                ]
            );

            // Create Mission
            $mission = Mission::updateOrCreate(
                ['slug' => Str::slug($mData['title'])],
                [
                    'adventure_world_id' => $mData['world']->id,
                    'lesson_id'          => $lesson->id,
                    'question_bank_id'   => $qBank->id,
                    'title'              => $mData['title'],
                    'display_title'      => $mData['title'],
                    'description'        => "Count {$plur} with Leo the Lion!",
                    'video_url'          => $videoUrl,
                    'status'             => 'published',
                    'sort_order'         => $mNum,
                    'pass_threshold_percent' => 60,
                    'stars_reward'       => 3,
                ]
            );

            // Delete old questions to re-seed cleanly
            $qBank->questions()->delete();

            // ── Questions 1 to $maxC: COUNT & TAP NUMBER ──
            for ($countTarget = 1; $countTarget <= $maxC; $countTarget++) {
                $emojis = str_repeat($mData['item_emoji'], $countTarget);
                $qText = "{$mData['prompt']} {$emojis}";

                $question = QuizQuestion::create([
                    'question_bank_id'   => $qBank->id,
                    'question_text'      => $qText,
                    'prompt_text'        => $qText,
                    'question_type'      => 'multiple_choice',
                    'audio_url'          => $promptAudioUrl,
                    'single_item_img_url'=> $singleItemImgUrl,
                    'target_count'       => $countTarget,
                    'sort_order'         => $countTarget,
                ]);

                // Create options (1, 2, 3...)
                $optionsArray = range(1, $maxC);
                foreach ($optionsArray as $optNum) {
                    QuestionOption::create([
                        'quiz_question_id' => $question->id,
                        'option_text'      => (string) $optNum,
                        'text_value'       => (string) $optNum,
                        'is_correct'       => ($optNum === $countTarget),
                    ]);
                }
            }

            // ── Questions $maxC + 1 to $maxC * 2: OPTION CARD CHOICE ──
            for ($cardTarget = 1; $cardTarget <= $maxC; $cardTarget++) {
                $itemName = ($cardTarget === 1) ? $mData['item_name'] : $mData['item_names'];
                $cardPromptText = "Which picture card shows {$cardTarget} {$itemName}? Tap it!";

                // Card Audio (e.g. 1_apple.mp3, 2_apples.mp3)
                $cardAudioKey = ($cardTarget === 1) ? "1_{$sing}" : "{$cardTarget}_{$plur}";
                $cardAudioUrl = $this->findMediaUrl([$cardAudioKey, "{$cardTarget}_{$sing}"]);

                $qIndex = $maxC + $cardTarget;
                $cardQuestion = QuizQuestion::create([
                    'question_bank_id'   => $qBank->id,
                    'question_text'      => $cardPromptText,
                    'prompt_text'        => $cardPromptText,
                    'question_type'      => 'multiple_choice',
                    'audio_url'          => $cardAudioUrl ?? $promptAudioUrl,
                    'sort_order'         => $qIndex,
                ]);

                // Options with picture card images
                for ($optCard = 1; $optCard <= $maxC; $optCard++) {
                    $optKey = ($optCard === 1) ? "1_{$sing}" : "{$optCard}_{$plur}";
                    $cardImgUrl = $this->findMediaUrl([$optKey, "{$optCard}_{$sing}"]);

                    QuestionOption::create([
                        'quiz_question_id' => $cardQuestion->id,
                        'option_text'      => "Card {$optCard}",
                        'text_value'       => (string) $optCard,
                        'image_url'        => $cardImgUrl,
                        'is_correct'       => ($optCard === $cardTarget),
                    ]);
                }
            }
        }
    }

    /**
     * Helper to find uploaded media public URL by matching filename or name.
     */
    protected function findMediaUrl(array $keywords): ?string
    {
        foreach ($keywords as $kw) {
            $media = Media::where('file_name', 'ILIKE', "%{$kw}%")
                ->orWhere('name', 'ILIKE', "%{$kw}%")
                ->first();

            if ($media) {
                return $media->url;
            }
        }

        return null;
    }
}
