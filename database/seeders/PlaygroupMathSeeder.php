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
use App\Models\Topic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlaygroupMathSeeder extends Seeder
{
    public function run(): void
    {
        $mathSubject = Subject::where('slug', 'like', 'mathematics%')->first()
            ?? Subject::firstOrCreate(['slug' => 'mathematics-pg'], ['name' => 'Mathematics Activities', 'code' => 'MATH']);

        $topic = Topic::firstOrCreate(
            ['slug' => 'counting-numbers-1-to-5-playgroup'],
            [
                'name' => 'Counting Numbers 1 to 5',
                'subject_id' => $mathSubject->id,
                'sort_order' => 1,
            ]
        );

        // 1. Ensure 3 Mathematics Worlds Exist
        $forestWorld = AdventureWorld::updateOrCreate(
            ['slug' => 'whispering-forest'],
            [
                'name' => 'Whispering Forest',
                'description' => 'Counting Numbers 1 to 3 with apples, animals, cars, and shiny stars!',
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
                'description' => 'Counting Numbers 1 to 4 with school bags, bears, and zooming cars!',
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
                'description' => 'Counting Numbers 1 to 5 with cookies, rabbits, fish, and zebras!',
                'icon' => '🍪',
                'theme_color' => '#EC4899',
                'subject_id' => $mathSubject->id,
                'sort_order' => 3,
                'is_locked' => false,
            ]
        );

        // 2. Clean up legacy test missions from all 3 Worlds
        $worldIds = [$forestWorld->id, $meadowWorld->id, $cookieWorld->id];
        $oldMissions = Mission::withTrashed()->whereIn('adventure_world_id', $worldIds)->get();
        foreach ($oldMissions as $oldM) {
            if ($oldM->questionBank) {
                $oldM->questionBank->questions()->withTrashed()->forceDelete();
                $oldM->questionBank->forceDelete();
            }
            $oldM->forceDelete();
        }

        // 3. Define All 20 Playgroup Mathematics Missions
        $missionsData = [
            // ── World 1: Whispering Forest (Missions 1 to 10 - Count 1..3) ──
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
                'item_name' => 'speedy red car',
                'item_names' => 'speedy red cars',
                'item_emoji' => '🚗',
                'prompt' => 'How many speedy red cars do you see? Tap their number!',
                'audio_prompt_key' => 'car_count',
                'max_count' => 3,
            ],
            [
                'world' => $forestWorld,
                'num' => 4,
                'title' => 'Playful Cat Counter 🐱',
                'item_singular' => 'cat',
                'item_plural' => 'cats',
                'item_name' => 'playful cat',
                'item_names' => 'playful cats',
                'item_emoji' => '🐱',
                'prompt' => 'How many playful cats do you see? Tap their number!',
                'audio_prompt_key' => 'cat_count',
                'max_count' => 3,
            ],
            [
                'world' => $forestWorld,
                'num' => 5,
                'title' => 'Glowing Star Counter ⭐',
                'item_singular' => 'star',
                'item_plural' => 'stars',
                'item_name' => 'glowing star',
                'item_names' => 'glowing stars',
                'item_emoji' => '⭐',
                'prompt' => 'How many glowing stars do you see? Tap their number!',
                'audio_prompt_key' => 'star_count',
                'max_count' => 3,
            ],
            [
                'world' => $forestWorld,
                'num' => 6,
                'title' => 'Floating Balloon Counter 🎈',
                'item_singular' => 'ballon',
                'item_plural' => 'ballons',
                'item_name' => 'floating balloon',
                'item_names' => 'floating balloons',
                'item_emoji' => '🎈',
                'prompt' => 'How many floating balloons do you see? Tap their number!',
                'audio_prompt_key' => 'ballon_count',
                'max_count' => 3,
            ],
            [
                'world' => $forestWorld,
                'num' => 7,
                'title' => 'Chirping Bird Counter 🐦',
                'item_singular' => 'bird',
                'item_plural' => 'birds',
                'item_name' => 'chirping bird',
                'item_names' => 'chirping birds',
                'item_emoji' => '🐦',
                'prompt' => 'How many chirping birds do you see? Tap their number!',
                'audio_prompt_key' => 'bird_count',
                'max_count' => 3,
            ],
            [
                'world' => $forestWorld,
                'num' => 8,
                'title' => 'Frosted Cake Counter 🍰',
                'item_singular' => 'cake',
                'item_plural' => 'cakes',
                'item_name' => 'frosted cake',
                'item_names' => 'frosted cakes',
                'item_emoji' => '🍰',
                'prompt' => 'How many frosted cakes do you see? Tap their number!',
                'audio_prompt_key' => 'cake_count',
                'max_count' => 3,
            ],
            [
                'world' => $forestWorld,
                'num' => 9,
                'title' => 'Playful Animal Counter 🐮',
                'item_singular' => 'animal',
                'item_plural' => 'animals',
                'item_name' => 'friendly animal',
                'item_names' => 'friendly animals',
                'item_emoji' => '🐮',
                'prompt' => 'How many friendly animals do you see? Tap their number!',
                'audio_prompt_key' => 'animal_count',
                'max_count' => 3,
            ],
            [
                'world' => $forestWorld,
                'num' => 10,
                'title' => 'Mixed Fruit Counter 🧺',
                'item_singular' => 'fruit',
                'item_plural' => 'fruits',
                'item_name' => 'yummy fruit',
                'item_names' => 'yummy fruits',
                'item_emoji' => '🧺',
                'prompt' => 'How many yummy fruits do you see? Tap their number!',
                'audio_prompt_key' => 'fruit_count',
                'max_count' => 3,
            ],

            // ── World 2: Sunny Meadow (Missions 11 to 15 - Count 1..4) ──
            [
                'world' => $meadowWorld,
                'num' => 11,
                'title' => 'School Bag Counter 🎒',
                'item_singular' => 'bag',
                'item_plural' => 'bags',
                'item_name' => 'school bag',
                'item_names' => 'school bags',
                'item_emoji' => '🎒',
                'prompt' => 'How many school bags do you see? Tap their number!',
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
                'title' => 'Chirping Bird Counter 🐦',
                'item_singular' => 'bird',
                'item_plural' => 'birds',
                'item_name' => 'chirping bird',
                'item_names' => 'chirping birds',
                'item_emoji' => '🐦',
                'prompt' => 'How many chirping birds do you see? Tap their number!',
                'audio_prompt_key' => 'bird_count',
                'max_count' => 4,
            ],
            [
                'world' => $meadowWorld,
                'num' => 14,
                'title' => 'Speedy Car Counter 🚗',
                'item_singular' => 'car',
                'item_plural' => 'cars',
                'item_name' => 'speedy red car',
                'item_names' => 'speedy red cars',
                'item_emoji' => '🚗',
                'prompt' => 'How many speedy red cars do you see? Tap their number!',
                'audio_prompt_key' => 'car_count',
                'max_count' => 4,
            ],
            [
                'world' => $meadowWorld,
                'num' => 15,
                'title' => 'Green Tree Counter 🌲',
                'item_singular' => 'tree',
                'item_plural' => 'trees',
                'item_name' => 'green tree',
                'item_names' => 'green trees',
                'item_emoji' => '🌲',
                'prompt' => 'How many green trees do you see? Tap their number!',
                'audio_prompt_key' => 'tree_count',
                'max_count' => 4,
            ],

            // ── World 3: Yummy Cookie Trail (Missions 16 to 20 - Count 1..5) ──
            [
                'world' => $cookieWorld,
                'num' => 16,
                'title' => 'Crunchy Cookie Counter 🍪',
                'item_singular' => 'cookie',
                'item_plural' => 'cookies',
                'item_name' => 'crunchy cookie',
                'item_names' => 'crunchy cookies',
                'item_emoji' => '🍪',
                'prompt' => 'How many crunchy cookies do you see? Tap their number!',
                'audio_prompt_key' => 'cookie_count',
                'max_count' => 5,
            ],
            [
                'world' => $cookieWorld,
                'num' => 17,
                'title' => 'Fluffy Rabbit Counter 🐰',
                'item_singular' => 'rabbit',
                'item_plural' => 'rabbits',
                'item_name' => 'fluffy rabbit',
                'item_names' => 'fluffy rabbits',
                'item_emoji' => '🐰',
                'prompt' => 'How many fluffy rabbits do you see? Tap their number!',
                'audio_prompt_key' => 'rabbit_count',
                'max_count' => 5,
            ],
            [
                'world' => $cookieWorld,
                'num' => 18,
                'title' => 'Swimming Fish Counter 🐠',
                'item_singular' => 'fish',
                'item_plural' => 'fishes',
                'item_name' => 'swimming fish',
                'item_names' => 'swimming fishes',
                'item_emoji' => '🐠',
                'prompt' => 'How many swimming fishes do you see? Tap their number!',
                'audio_prompt_key' => 'fish_count',
                'max_count' => 5,
            ],
            [
                'world' => $cookieWorld,
                'num' => 19,
                'title' => 'Striped Zebra Counter 🦓',
                'item_singular' => 'zebra',
                'item_plural' => 'zebras',
                'item_name' => 'striped zebra',
                'item_names' => 'striped zebras',
                'item_emoji' => '🦓',
                'prompt' => 'How many striped zebras do you see? Tap their number!',
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

        // 4. Process Each Mission and Wire Uploaded Media
        foreach ($missionsData as $mData) {
            $mNum = $mData['num'];
            $sing = $mData['item_singular'];
            $plur = $mData['item_plural'];
            $maxC = $mData['max_count'];

            // Find uploaded video for this mission (e.g. Pg Math 1.Mp4 -> ID 176)
            $videoUrl = $this->findMediaUrl('video', [
                "Pg Math {$mNum}",
                "Pg Math{$mNum}",
            ]);

            // Find counting prompt voiceover audio
            $promptAudioUrl = $this->findMediaUrl('audio', [
                "{$sing}_count",
                "1_{$sing}",
                "count_{$sing}",
                "{$sing}",
            ]);

            // Find single item image (e.g. 1_apple.jpg -> ID 13/33, 1_banana.jpg -> ID 16/36)
            $singleItemImgUrl = $this->findMediaUrl('image', [
                "1_{$sing}",
                "{$sing}.jpg",
                $sing,
            ]);

            // Create Question Bank
            $qBank = QuestionBank::create([
                'name'        => "Question Bank — {$mData['title']}",
                'subject_id'  => $mathSubject->id,
                'description' => "Questions for {$mData['title']}",
            ]);

            // Find or create Lesson for this mission
            $lesson = Lesson::firstOrCreate(
                ['slug' => Str::slug($mData['title'])],
                [
                    'topic_id'   => $topic->id,
                    'title'      => $mData['title'],
                    'sort_order' => $mNum,
                ]
            );

            // Create or restore Mission
            $mission = Mission::withTrashed()->updateOrCreate(
                ['slug' => Str::slug($mData['title'])],
                [
                    'adventure_world_id'     => $mData['world']->id,
                    'lesson_id'              => $lesson->id,
                    'question_bank_id'       => $qBank->id,
                    'title'                  => $mData['title'],
                    'display_title'          => $mData['title'],
                    'description'            => "Count {$plur} with Leo the Lion!",
                    'video_url'              => $videoUrl,
                    'status'                 => 'published',
                    'sort_order'             => $mNum,
                    'pass_threshold_percent' => 60,
                    'stars_reward'           => 3,
                ]
            );
            if ($mission->trashed()) {
                $mission->restore();
            }

            // Resolve Quiz Types
            $countTypeId = \App\Models\QuizType::where('code', 'QT-09')->value('id') ?? 9;
            $mcTypeId    = \App\Models\QuizType::where('code', 'QT-01')->value('id') ?? 1;

            // ── Questions 1 to $maxC: COUNT & TAP NUMBER ──
            for ($countTarget = 1; $countTarget <= $maxC; $countTarget++) {
                $emojis = str_repeat($mData['item_emoji'], $countTarget);
                $qText = "{$mData['prompt']} {$emojis}";

                $question = QuizQuestion::create([
                    'question_bank_id' => $qBank->id,
                    'quiz_type_id'     => $countTypeId,
                    'prompt'           => $qText,
                    'prompt_audio_url' => $promptAudioUrl,
                    'points'           => 1,
                    'sort_order'       => $countTarget,
                    'scoring_config'   => [
                        'count'        => $countTarget,
                        'target_count' => $countTarget,
                        'image_url'    => $singleItemImgUrl,
                    ],
                ]);

                // Create options (1, 2, 3...)
                $optionsArray = range(1, $maxC);
                foreach ($optionsArray as $optIdx => $optNum) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'text_value'  => (string) $optNum,
                        'is_correct'  => ($optNum === $countTarget),
                        'sort_order'  => $optIdx + 1,
                    ]);
                }
            }

            // ── Questions $maxC + 1 to $maxC * 2: OPTION CARD CHOICE ──
            for ($cardTarget = 1; $cardTarget <= $maxC; $cardTarget++) {
                $itemName = ($cardTarget === 1) ? $mData['item_name'] : $mData['item_names'];
                $cardPromptText = "Which picture card shows {$cardTarget} {$itemName}? Tap it!";

                // Card Audio (type: audio)
                $cardAudioKey = ($cardTarget === 1) ? "1_{$sing}" : "{$cardTarget}_{$plur}";
                $cardAudioUrl = $this->findMediaUrl('audio', [$cardAudioKey, "{$cardTarget}_{$sing}"]);

                $qIndex = $maxC + $cardTarget;
                $cardQuestion = QuizQuestion::create([
                    'question_bank_id' => $qBank->id,
                    'quiz_type_id'     => $mcTypeId,
                    'prompt'           => $cardPromptText,
                    'prompt_audio_url' => $cardAudioUrl ?? $promptAudioUrl,
                    'points'           => 1,
                    'sort_order'       => $qIndex,
                ]);

                // Options with picture card images
                for ($optCard = 1; $optCard <= $maxC; $optCard++) {
                    $cardKey = ($optCard === 1) ? "1_{$sing}" : "{$optCard}_{$plur}";
                    $cardPrefixKey = "card_{$optCard}_{$sing}";

                    $cardImgUrl  = $this->findMediaUrl('image', [$cardPrefixKey, $cardKey, "{$optCard}_{$sing}"]);
                    $optAudioUrl = $this->findMediaUrl('audio', [$cardKey, "{$optCard}_{$sing}"]);

                    QuestionOption::create([
                        'question_id' => $cardQuestion->id,
                        'text_value'  => (string) $optCard,
                        'image_url'   => $cardImgUrl,
                        'audio_url'   => $optAudioUrl,
                        'is_correct'  => ($optCard === $cardTarget),
                        'sort_order'  => $optCard,
                    ]);
                }
            }
        }
    }

    /**
     * Helper to find uploaded media public URL by case-insensitive type and keyword search.
     */
    protected function findMediaUrl(string $type, array $keywords): ?string
    {
        foreach ($keywords as $kw) {
            $media = Media::where('type', 'ILIKE', "%{$type}%")
                ->where(function ($q) use ($kw) {
                    $q->where('name', 'ILIKE', "%{$kw}%")
                      ->orWhere('file_name', 'ILIKE', "%{$kw}%")
                      ->orWhere('file_path', 'ILIKE', "%{$kw}%");
                })
                ->first();

            if ($media) {
                return $media->url;
            }
        }

        return null;
    }
}
