<?php

namespace Database\Seeders;

use App\Models\AdventureWorld;
use App\Models\Lesson;
use App\Models\Mission;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\QuizQuestion;
use App\Models\QuizType;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CreMissionsSeeder extends Seeder
{
    public function run(?int $batch = null): void
    {
        // 1. CRE Subject & Topic
        $creSubject = Subject::where('slug', 'like', '%religious%')
            ->orWhere('slug', 'like', '%cre%')
            ->first() ?? Subject::create([
                'name' => 'Christian Religious Education',
                'slug' => 'christian-religious-education-pg',
                'code' => 'CRE',
            ]);

        $topic = Topic::firstOrCreate(
            ['slug' => 'cre-playgroup-foundation'],
            [
                'name' => 'CRE Foundation & Daily Values',
                'subject_id' => $creSubject->id,
                'sort_order' => 1,
            ]
        );

        // 2. Adventure Worlds
        $creationWorld = AdventureWorld::updateOrCreate(
            ['slug' => 'creation-realm'],
            [
                'name' => 'Creation Realm',
                'description' => "Explore God's beautiful world, sky, animals, and nature!",
                'icon' => '☀️',
                'theme_color' => '#10B981',
                'subject_id' => $creSubject->id,
                'sort_order' => 1,
                'is_locked' => false,
            ]
        );

        $jesusWorld = AdventureWorld::updateOrCreate(
            ['slug' => 'jesus-realm'],
            [
                'name' => 'Jesus Realm',
                'description' => 'Discover the love, miracles, and story of Jesus!',
                'icon' => '✝️',
                'theme_color' => '#8B5CF6',
                'subject_id' => $creSubject->id,
                'sort_order' => 2,
                'is_locked' => false,
            ]
        );

        $valuesWorld = AdventureWorld::updateOrCreate(
            ['slug' => 'christian-values-realm'],
            [
                'name' => 'Christian Values Realm',
                'description' => 'Learn kindness, sharing, helping at home, and honesty!',
                'icon' => '🤝',
                'theme_color' => '#EC4899',
                'subject_id' => $creSubject->id,
                'sort_order' => 3,
                'is_locked' => false,
            ]
        );

        // Quiz Types
        $mcTypeId = QuizType::where('code', 'QT-01')->value('id') ?? 1;
        $tfTypeId = QuizType::where('code', 'QT-03')->value('id') ?? 3;
        $matchTypeId = QuizType::where('code', 'QT-05')->value('id') ?? 5;
        $sortTypeId = QuizType::where('code', 'QT-04')->value('id') ?? 4;

        // Determine range based on batch
        $startMission = 1;
        $endMission = 25;

        if ($batch === 1) { $startMission = 1; $endMission = 5; }
        elseif ($batch === 2) { $startMission = 6; $endMission = 10; }
        elseif ($batch === 3) { $startMission = 11; $endMission = 15; }
        elseif ($batch === 4) { $startMission = 16; $endMission = 20; }
        elseif ($batch === 5) { $startMission = 21; $endMission = 25; }

        // 3. Complete Mission Data Definitions
        $allMissions = [
            // REALM 1: CREATION (M1 - M10)
            1 => [
                'world' => $creationWorld, 'title' => 'God Made Sky & Sun ☀️',
                'q_imgs' => ['cre_m1_sun.webp', 'cre_m1_sun.webp', 'cre_m1_sky.webp', 'cre_m1_cloud.webp', 'cre_m1_sun.webp', 'cre_m1_sky.webp', 'cre_m1_sun.webp', 'cre_m1_sun.webp'],
                'distractors' => ['cre_m1_cloud.webp', 'cre_m1_sky.webp', 'cre_m1_sun.webp', 'cre_m1_sky.webp']
            ],
            2 => [
                'world' => $creationWorld, 'title' => 'God Made Trees & Flowers 🌸',
                'q_imgs' => ['cre_m2_flower.webp', 'cre_m2_flower.webp', 'cre_m2_tree.webp', 'cre_m2_grass.webp', 'cre_m2_flower.webp', 'cre_m2_tree.webp', 'cre_m2_tree.webp', 'cre_m2_flower.webp'],
                'distractors' => ['cre_m2_tree.webp', 'cre_m2_grass.webp', 'cre_m2_flower.webp', 'cre_m2_tree.webp']
            ],
            3 => [
                'world' => $creationWorld, 'title' => 'God Made Animals & Birds 🦁',
                'q_imgs' => ['cre_m3_lion.webp', 'cre_m3_bird.webp', 'cre_m3_fish.webp', 'cre_m3_lion.webp', 'cre_m3_lion.webp', 'cre_m3_bird.webp', 'cre_m3_lion.webp', 'cre_m3_bird.webp'],
                'distractors' => ['cre_m3_bird.webp', 'cre_m3_fish.webp', 'cre_m3_lion.webp', 'cre_m3_fish.webp']
            ],
            4 => [
                'world' => $creationWorld, 'title' => 'God Made Water & Rivers 🌊',
                'q_imgs' => ['cre_m4_river.webp', 'cre_m4_rain.webp', 'cre_m4_glass_water.webp', 'cre_m4_river.webp', 'cre_m4_rain.webp', 'cre_m4_rain.webp', 'cre_m4_glass_water.webp', 'cre_m4_river.webp'],
                'distractors' => ['cre_m4_rain.webp', 'cre_m4_glass_water.webp', 'cre_m4_river.webp', 'cre_m4_rain.webp']
            ],
            5 => [
                'world' => $creationWorld, 'title' => 'God Made Me Unique 👧👦',
                'q_imgs' => ['cre_m5_boy.webp', 'cre_m5_girl.webp', 'cre_m5_boy.webp', 'cre_m5_heart.webp', 'cre_m5_heart.webp', 'cre_m5_boy.webp', 'cre_m5_girl.webp', 'cre_m5_boy.webp'],
                'distractors' => ['cre_m5_girl.webp', 'cre_m5_boy.webp', 'cre_m5_heart.webp', 'cre_m5_girl.webp']
            ],
            6 => [
                'world' => $creationWorld, 'title' => 'God Made My Body 🖐️',
                'q_imgs' => ['cre_m6_eyes.webp', 'cre_m6_ears.webp', 'cre_m6_hands.webp', 'cre_m6_hands.webp', 'cre_m6_eyes.webp', 'cre_m6_ears.webp', 'cre_m6_hands.webp', 'cre_m6_hands.webp'],
                'distractors' => ['cre_m6_ears.webp', 'cre_m6_hands.webp', 'cre_m6_eyes.webp', 'cre_m6_ears.webp']
            ],
            7 => [
                'world' => $creationWorld, 'title' => 'God Loves My Family 👨‍👩‍👧‍👦',
                'q_imgs' => ['cre_m7_family.webp', 'cre_m7_mommy.webp', 'cre_m7_daddy.webp', 'cre_m7_family.webp', 'cre_m7_family.webp', 'cre_m7_mommy.webp', 'cre_m7_family.webp', 'cre_m7_daddy.webp'],
                'distractors' => ['cre_m7_mommy.webp', 'cre_m7_daddy.webp', 'cre_m7_family.webp', 'cre_m7_mommy.webp']
            ],
            8 => [
                'world' => $creationWorld, 'title' => 'Thanking God in Prayer 🙏',
                'q_imgs' => ['cre_m8_praying.webp', 'cre_m8_meal_pray.webp', 'cre_m8_bed_pray.webp', 'cre_m8_praying.webp', 'cre_m8_praying.webp', 'cre_m8_praying.webp', 'cre_m8_meal_pray.webp', 'cre_m8_bed_pray.webp'],
                'distractors' => ['cre_m8_meal_pray.webp', 'cre_m8_bed_pray.webp', 'cre_m8_praying.webp', 'cre_m8_meal_pray.webp']
            ],
            9 => [
                'world' => $creationWorld, 'title' => "Caring for God's Creation 🌱",
                'q_imgs' => ['cre_m9_water_plant.webp', 'cre_m9_feed_birds.webp', 'cre_m9_clean_litter.webp', 'cre_m9_water_plant.webp', 'cre_m9_water_plant.webp', 'cre_m9_feed_birds.webp', 'cre_m9_clean_litter.webp', 'cre_m9_water_plant.webp'],
                'distractors' => ['cre_m9_feed_birds.webp', 'cre_m9_clean_litter.webp', 'cre_m9_water_plant.webp', 'cre_m9_feed_birds.webp']
            ],
            10 => [
                'world' => $creationWorld, 'title' => 'Creation Realm Master 🏆',
                'q_imgs' => ['cre_m10_globe.webp', 'cre_m10_rainbow.webp', 'cre_m10_crown.webp', 'cre_m10_crown.webp', 'cre_m10_globe.webp', 'cre_m10_crown.webp', 'cre_m10_rainbow.webp', 'cre_m10_globe.webp'],
                'distractors' => ['cre_m10_rainbow.webp', 'cre_m10_crown.webp', 'cre_m10_globe.webp', 'cre_m10_rainbow.webp']
            ],

            // REALM 2: JESUS STORIES (M11 - M20)
            11 => [
                'world' => $jesusWorld, 'title' => 'Jesus Loves the Children 👧🧒',
                'pair1' => 'cre_m11_jesus_kids.webp', 'pair2' => 'cre_m11_happy_child.webp',
                'q_imgs' => ['cre_m11_jesus_kids.webp', 'cre_m11_happy_child.webp', 'cre_m11_heart_love.webp', 'cre_m11_happy_child.webp'],
                'distractors' => ['cre_m11_happy_child.webp', 'cre_m11_heart_love.webp', 'cre_m11_jesus_kids.webp', 'cre_m11_heart_love.webp']
            ],
            12 => [
                'world' => $jesusWorld, 'title' => 'Baby Jesus in the Manger 👶⭐',
                'pair1' => 'cre_m12_manger.webp', 'pair2' => 'cre_m12_star.webp',
                'q_imgs' => ['cre_m12_manger.webp', 'cre_m12_manger.webp', 'cre_m12_star.webp', 'cre_m12_mary.webp'],
                'distractors' => ['cre_m12_star.webp', 'cre_m12_mary.webp', 'cre_m12_manger.webp', 'cre_m12_star.webp']
            ],
            13 => [
                'world' => $jesusWorld, 'title' => 'Jesus Helps the Sick 🩺❤️',
                'pair1' => 'cre_m13_healing.webp', 'pair2' => 'cre_m13_healthy_child.webp',
                'q_imgs' => ['cre_m13_healing.webp', 'cre_m13_healthy_child.webp', 'cre_m13_heart.webp', 'cre_m13_healing.webp'],
                'distractors' => ['cre_m13_healthy_child.webp', 'cre_m13_heart.webp', 'cre_m13_healing.webp', 'cre_m13_heart.webp']
            ],
            14 => [
                'world' => $jesusWorld, 'title' => 'Jesus Calms the Storm ⛵🌊',
                'pair1' => 'cre_m14_jesus_ship.webp', 'pair2' => 'cre_m14_boat.webp',
                'q_imgs' => ['cre_m14_jesus_ship.webp', 'cre_m14_boat.webp', 'cre_m14_calm_water.webp', 'cre_m14_calm_water.webp'],
                'distractors' => ['cre_m14_boat.webp', 'cre_m14_calm_water.webp', 'cre_m14_jesus_ship.webp', 'cre_m14_boat.webp']
            ],
            15 => [
                'world' => $jesusWorld, 'title' => 'Jesus Feeds 5,000 🍞🐟',
                'pair1' => 'cre_m15_basket.webp', 'pair2' => 'cre_m15_bread.webp',
                'q_imgs' => ['cre_m15_basket.webp', 'cre_m15_bread.webp', 'cre_m15_fish.webp', 'cre_m15_basket.webp'],
                'distractors' => ['cre_m15_bread.webp', 'cre_m15_fish.webp', 'cre_m15_basket.webp', 'cre_m15_fish.webp']
            ],
            16 => [
                'world' => $jesusWorld, 'title' => 'Jesus the Good Shepherd 🐑',
                'pair1' => 'cre_m16_shepherd.webp', 'pair2' => 'cre_m16_lamb.webp',
                'q_imgs' => ['cre_m16_shepherd.webp', 'cre_m16_lamb.webp', 'cre_m16_meadow.webp', 'cre_m16_shepherd.webp'],
                'distractors' => ['cre_m16_lamb.webp', 'cre_m16_meadow.webp', 'cre_m16_shepherd.webp', 'cre_m16_meadow.webp']
            ],
            17 => [
                'world' => $jesusWorld, 'title' => 'Jesus is My Kind Friend 🤝',
                'pair1' => 'cre_m17_friend.webp', 'pair2' => 'cre_m17_hug.webp',
                'q_imgs' => ['cre_m17_friend.webp', 'cre_m17_hug.webp', 'cre_m17_handshake.webp', 'cre_m17_hug.webp'],
                'distractors' => ['cre_m17_hug.webp', 'cre_m17_handshake.webp', 'cre_m17_friend.webp', 'cre_m17_handshake.webp']
            ],
            18 => [
                'world' => $jesusWorld, 'title' => 'Jesus Prays to Father 🙏✨',
                'pair1' => 'cre_m18_jesus_pray.webp', 'pair2' => 'cre_m18_light.webp',
                'q_imgs' => ['cre_m18_jesus_pray.webp', 'cre_m18_light.webp', 'cre_m18_mountain.webp', 'cre_m18_jesus_pray.webp'],
                'distractors' => ['cre_m18_light.webp', 'cre_m18_mountain.webp', 'cre_m18_jesus_pray.webp', 'cre_m18_light.webp']
            ],
            19 => [
                'world' => $jesusWorld, 'title' => 'Resurrection Joy & Easter ✝️🌸',
                'pair1' => 'cre_m19_tomb.webp', 'pair2' => 'cre_m19_sunrise.webp',
                'q_imgs' => ['cre_m19_tomb.webp', 'cre_m19_sunrise.webp', 'cre_m19_angel.webp', 'cre_m19_angel.webp'],
                'distractors' => ['cre_m19_sunrise.webp', 'cre_m19_angel.webp', 'cre_m19_tomb.webp', 'cre_m19_sunrise.webp']
            ],
            20 => [
                'world' => $jesusWorld, 'title' => 'Jesus Story Master 🏆',
                'pair1' => 'cre_m20_bible.webp', 'pair2' => 'cre_m20_cross.webp',
                'q_imgs' => ['cre_m20_bible.webp', 'cre_m20_cross.webp', 'cre_m20_trophy.webp', 'cre_m20_trophy.webp'],
                'distractors' => ['cre_m20_cross.webp', 'cre_m20_trophy.webp', 'cre_m20_bible.webp', 'cre_m20_cross.webp']
            ],

            // REALM 3: CHRISTIAN VALUES (M21 - M25)
            21 => [
                'world' => $valuesWorld, 'title' => 'Sharing Toys with Friends 🧸',
                'cat1' => 'Toy Basket 🧺', 'cat2' => 'Trash Bin 🗑️',
                'b1_img1' => 'cre_m21_teddy_bear.webp', 'b1_img2' => 'cre_m21_toy_car.webp', 'b1_img3' => 'cre_m21_blocks.webp',
                'b2_img1' => 'cre_m21_apple_core.webp',
                'q_imgs' => ['cre_m21_teddy_bear.webp', 'cre_m21_blocks.webp', 'cre_m21_toy_basket.webp', 'cre_m21_toy_car.webp'],
                'distractors' => ['cre_m21_toy_car.webp', 'cre_m21_toy_basket.webp', 'cre_m21_teddy_bear.webp', 'cre_m21_blocks.webp']
            ],
            22 => [
                'world' => $valuesWorld, 'title' => 'Saying Thank You & Please 😊',
                'cat1' => 'Happy Box 😊', 'cat2' => 'Sad Box 😢',
                'b1_img1' => 'cre_m22_thank_you_smile.webp', 'b1_img2' => 'cre_m22_flower_gift.webp', 'b1_img3' => 'cre_m22_please_hands.webp',
                'b2_img1' => 'cre_m22_crying_child.webp',
                'q_imgs' => ['cre_m22_thank_you_smile.webp', 'cre_m22_please_hands.webp', 'cre_m22_flower_gift.webp', 'cre_m22_happy_box.webp'],
                'distractors' => ['cre_m22_please_hands.webp', 'cre_m22_flower_gift.webp', 'cre_m22_thank_you_smile.webp', 'cre_m22_please_hands.webp']
            ],
            23 => [
                'world' => $valuesWorld, 'title' => 'Helping Family at Home 🧹',
                'cat1' => 'Cleaning Bucket 🪣', 'cat2' => 'Toy Box 📦',
                'b1_img1' => 'cre_m23_broom.webp', 'b1_img2' => 'cre_m23_dustpan.webp',
                'b2_img1' => 'cre_m23_teddy_bear.webp', 'b2_img2' => 'cre_m23_toy_car.webp',
                'q_imgs' => ['cre_m23_broom.webp', 'cre_m23_broom.webp', 'cre_m23_toy_box.webp', 'cre_m23_cleaning_bucket.webp'],
                'distractors' => ['cre_m23_dustpan.webp', 'cre_m23_cleaning_bucket.webp', 'cre_m23_broom.webp', 'cre_m23_dustpan.webp']
            ],
            24 => [
                'world' => $valuesWorld, 'title' => 'Obeying Parents & Teachers 👂',
                'cat1' => 'Class Box 🪑', 'cat2' => 'Stop Box 🛑',
                'b1_img1' => 'cre_m24_sitting_quietly.webp', 'b1_img2' => 'cre_m24_raising_hand.webp',
                'b2_img1' => 'cre_m24_standing_on_table.webp', 'b2_img2' => 'cre_m24_screaming_child.webp',
                'q_imgs' => ['cre_m24_sitting_quietly.webp', 'cre_m24_raising_hand.webp', 'cre_m24_class_box.webp', 'cre_m24_stop_box.webp'],
                'distractors' => ['cre_m24_raising_hand.webp', 'cre_m24_class_box.webp', 'cre_m24_sitting_quietly.webp', 'cre_m24_raising_hand.webp']
            ],
            25 => [
                'world' => $valuesWorld, 'title' => 'Being Honest & Truthful 💖',
                'cat1' => 'Star Box ⭐️', 'cat2' => 'Wrong Box ❌',
                'b1_img1' => 'cre_m25_return_toy.webp', 'b1_img2' => 'cre_m25_telling_truth.webp', 'b1_img3' => 'cre_m25_star_badge.webp',
                'b2_img1' => 'cre_m25_hiding_toy.webp',
                'q_imgs' => ['cre_m25_return_toy.webp', 'cre_m25_telling_truth.webp', 'cre_m25_star_badge.webp', 'cre_m25_star_box.webp'],
                'distractors' => ['cre_m25_telling_truth.webp', 'cre_m25_star_badge.webp', 'cre_m25_return_toy.webp', 'cre_m25_telling_truth.webp']
            ],
        ];

        // 4. Seed Missions in Specified Range
        for ($mNum = $startMission; $mNum <= $endMission; $mNum++) {
            if (!isset($allMissions[$mNum])) continue;
            $mData = $allMissions[$mNum];

            $qBank = QuestionBank::create([
                'name' => "Question Bank — {$mData['title']}",
                'subject_id' => $creSubject->id,
                'description' => "Questions for {$mData['title']}",
            ]);

            $lesson = Lesson::firstOrCreate(
                ['slug' => Str::slug($mData['title'])],
                [
                    'topic_id' => $topic->id,
                    'title' => $mData['title'],
                    'sort_order' => $mNum,
                ]
            );

            $mission = Mission::withTrashed()->updateOrCreate(
                ['slug' => Str::slug($mData['title'])],
                [
                    'adventure_world_id' => $mData['world']->id,
                    'lesson_id' => $lesson->id,
                    'question_bank_id' => $qBank->id,
                    'title' => $mData['title'],
                    'display_title' => $mData['title'],
                    'description' => "Learn about {$mData['title']} with fun activities!",
                    'video_url' => "/videos/cre_m{$mNum}_intro.mp4",
                    'status' => 'published',
                    'sort_order' => $mNum,
                    'deleted_at' => null,
                ]
            );

            $this->seedQuestionsForMission($qBank, $mNum, $mData, $mcTypeId, $tfTypeId, $matchTypeId, $sortTypeId);
        }

        $this->command->info("🎉 Successfully seeded CRE Missions {$startMission} to {$endMission}!");
    }

    private function seedQuestionsForMission($qBank, $mNum, $mData, $mcTypeId, $tfTypeId, $matchTypeId, $sortTypeId): void
    {
        for ($q = 1; $q <= 8; $q++) {
            $audioUrl = "/audio/m11/cre_m{$mNum}_q{$q}.mp3";

            if ($mNum <= 10) {
                // Missions 1 to 10: 4x Multiple Choice + 4x True/False
                if ($q <= 4) {
                    $imgFile = $mData['q_imgs'][$q - 1];
                    $disFile = $mData['distractors'][$q - 1];
                    $imgUrl = "/images/m11/{$imgFile}";
                    $disUrl = "/images/m11/{$disFile}";
                    
                    $question = QuizQuestion::create([
                        'question_bank_id' => $qBank->id,
                        'quiz_type_id' => $mcTypeId,
                        'prompt' => 'Touch the correct picture!',
                        'prompt_audio_url' => $audioUrl,
                        'prompt_image_url' => $imgUrl,
                        'points' => 10,
                        'sort_order' => $q,
                    ]);

                    QuestionOption::create(['question_id' => $question->id, 'text_value' => 'Correct Choice', 'image_url' => $imgUrl, 'is_correct' => true, 'sort_order' => 1]);
                    QuestionOption::create(['question_id' => $question->id, 'text_value' => 'Wrong Choice', 'image_url' => $disUrl, 'is_correct' => false, 'sort_order' => 2]);
                } else {
                    $imgFile = $mData['q_imgs'][$q - 1];
                    $imgUrl = "/images/m11/{$imgFile}";

                    $question = QuizQuestion::create([
                        'question_bank_id' => $qBank->id,
                        'quiz_type_id' => $tfTypeId,
                        'prompt' => 'Touch Yes or No!',
                        'prompt_audio_url' => $audioUrl,
                        'prompt_image_url' => $imgUrl,
                        'points' => 10,
                        'sort_order' => $q,
                    ]);

                    $isYes = ($q <= 7);
                    QuestionOption::create(['question_id' => $question->id, 'text_value' => '🟢 YES', 'is_correct' => $isYes, 'sort_order' => 1]);
                    QuestionOption::create(['question_id' => $question->id, 'text_value' => '🔴 NO', 'is_correct' => !$isYes, 'sort_order' => 2]);
                }
            } elseif ($mNum <= 20) {
                // Missions 11 to 20: 4x Multiple Choice + 4x 2D Image Matching
                if ($q <= 4) {
                    $imgFile = $mData['q_imgs'][$q - 1];
                    $disFile = $mData['distractors'][$q - 1];
                    $imgUrl = "/images/m11/{$imgFile}";
                    $disUrl = "/images/m11/{$disFile}";

                    $question = QuizQuestion::create([
                        'question_bank_id' => $qBank->id,
                        'quiz_type_id' => $mcTypeId,
                        'prompt' => 'Touch the correct picture!',
                        'prompt_audio_url' => $audioUrl,
                        'prompt_image_url' => $imgUrl,
                        'points' => 10,
                        'sort_order' => $q,
                    ]);

                    QuestionOption::create(['question_id' => $question->id, 'text_value' => 'Correct Choice', 'image_url' => $imgUrl, 'is_correct' => true, 'sort_order' => 1]);
                    QuestionOption::create(['question_id' => $question->id, 'text_value' => 'Wrong Choice', 'image_url' => $disUrl, 'is_correct' => false, 'sort_order' => 2]);
                } else {
                    $p1File = $mData['pair1'];
                    $p2File = $mData['pair2'];
                    $p1Url = "/images/m11/{$p1File}";
                    $p2Url = "/images/m11/{$p2File}";

                    $question = QuizQuestion::create([
                        'question_bank_id' => $qBank->id,
                        'quiz_type_id' => $matchTypeId,
                        'prompt' => 'Match matching pictures!',
                        'prompt_audio_url' => $audioUrl,
                        'prompt_image_url' => $p1Url,
                        'points' => 10,
                        'sort_order' => $q,
                    ]);

                    // Pair 1
                    QuestionOption::create(['question_id' => $question->id, 'text_value' => 'Card 1', 'image_url' => $p1Url, 'match_key' => 'pair_1', 'is_correct' => true, 'sort_order' => 1]);
                    QuestionOption::create(['question_id' => $question->id, 'text_value' => 'Match 1', 'image_url' => $p1Url, 'match_key' => 'pair_1', 'is_correct' => true, 'sort_order' => 2]);
                    // Pair 2
                    QuestionOption::create(['question_id' => $question->id, 'text_value' => 'Card 2', 'image_url' => $p2Url, 'match_key' => 'pair_2', 'is_correct' => true, 'sort_order' => 3]);
                    QuestionOption::create(['question_id' => $question->id, 'text_value' => 'Match 2', 'image_url' => $p2Url, 'match_key' => 'pair_2', 'is_correct' => true, 'sort_order' => 4]);
                }
            } else {
                // Missions 21 to 25: 4x Multiple Choice + 4x Category Sorting
                if ($q <= 4) {
                    $imgFile = $mData['q_imgs'][$q - 1];
                    $disFile = $mData['distractors'][$q - 1];
                    $imgUrl = "/images/m11/{$imgFile}";
                    $disUrl = "/images/m11/{$disFile}";

                    $question = QuizQuestion::create([
                        'question_bank_id' => $qBank->id,
                        'quiz_type_id' => $mcTypeId,
                        'prompt' => 'Touch the correct picture!',
                        'prompt_audio_url' => $audioUrl,
                        'prompt_image_url' => $imgUrl,
                        'points' => 10,
                        'sort_order' => $q,
                    ]);

                    QuestionOption::create(['question_id' => $question->id, 'text_value' => 'Correct Choice', 'image_url' => $imgUrl, 'is_correct' => true, 'sort_order' => 1]);
                    QuestionOption::create(['question_id' => $question->id, 'text_value' => 'Wrong Choice', 'image_url' => $disUrl, 'is_correct' => false, 'sort_order' => 2]);
                } else {
                    $cat1Name = $mData['cat1'];
                    $cat2Name = $mData['cat2'];

                    $b1_1 = "/images/m11/" . $mData['b1_img1'];
                    $b1_2 = "/images/m11/" . ($mData['b1_img2'] ?? $mData['b1_img1']);
                    $b1_3 = "/images/m11/" . ($mData['b1_img3'] ?? $mData['b1_img1']);
                    $b2_1 = "/images/m11/" . $mData['b2_img1'];

                    $question = QuizQuestion::create([
                        'question_bank_id' => $qBank->id,
                        'quiz_type_id' => $sortTypeId,
                        'prompt' => 'Sort items into the right boxes!',
                        'prompt_audio_url' => $audioUrl,
                        'prompt_image_url' => $b1_1,
                        'metadata' => [
                            'categories' => [$cat1Name, $cat2Name]
                        ],
                        'points' => 10,
                        'sort_order' => $q,
                    ]);

                    QuestionOption::create(['question_id' => $question->id, 'text_value' => $cat1Name, 'image_url' => $b1_1, 'match_key' => $cat1Name, 'is_correct' => true, 'sort_order' => 1]);
                    QuestionOption::create(['question_id' => $question->id, 'text_value' => $cat1Name, 'image_url' => $b1_2, 'match_key' => $cat1Name, 'is_correct' => true, 'sort_order' => 2]);
                    QuestionOption::create(['question_id' => $question->id, 'text_value' => $cat1Name, 'image_url' => $b1_3, 'match_key' => $cat1Name, 'is_correct' => true, 'sort_order' => 3]);
                    QuestionOption::create(['question_id' => $question->id, 'text_value' => $cat2Name, 'image_url' => $b2_1, 'match_key' => $cat2Name, 'is_correct' => true, 'sort_order' => 4]);
                }
            }
        }
    }
}
