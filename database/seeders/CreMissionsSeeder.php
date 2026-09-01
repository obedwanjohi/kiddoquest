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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreMissionsSeeder extends Seeder
{
    public function run(?int $worldOrBatch = null, ?int $singleMission = null): void
    {
        // 1. CRE Subject & Topic
        $pgLevel = \App\Models\Level::where('code', 'PG')->first() 
            ?? \App\Models\Level::where('slug', 'like', '%play%')->first();

        $creSubject = Subject::where('slug', 'like', '%religious%')
            ->orWhere('slug', 'like', '%cre%')
            ->first() ?? Subject::create([
                'name' => 'Christian Religious Education',
                'slug' => 'christian-religious-education-pg',
                'code' => 'CRE',
                'level_id' => $pgLevel?->id,
            ]);

        if ($pgLevel && !$creSubject->level_id) {
            $creSubject->update(['level_id' => $pgLevel->id]);
        }

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

        // Ensure Quiz Types are properly resolved with 100% precision
        $mcType = QuizType::where('slug', 'multiple-choice')->orWhere('slug', 'multiple_choice')->orWhere('code', 'QT-01')->first()
            ?? QuizType::firstOrCreate(
                ['slug' => 'multiple-choice'],
                ['code' => 'QT-01', 'name' => 'Multiple Choice', 'interaction_mode' => 'tap', 'has_options' => true, 'is_scoring_type' => true]
            );

        $tfType = QuizType::where('slug', 'true-false')->orWhere('slug', 'true_false')->orWhere('code', 'QT-02')->first()
            ?? QuizType::firstOrCreate(
                ['slug' => 'true-false'],
                ['code' => 'QT-02', 'name' => 'True / False', 'interaction_mode' => 'tap', 'has_options' => true, 'is_scoring_type' => true]
            );

        $mcTypeId = $mcType->id;
        $tfTypeId = $tfType->id;

        // Determine range based on parameters
        $startMission = 1;
        $endMission = 25;

        if ($singleMission) {
            $startMission = $singleMission;
            $endMission = $singleMission;
        } elseif ($worldOrBatch === 1) { $startMission = 1; $endMission = 10; }
        elseif ($worldOrBatch === 2) { $startMission = 11; $endMission = 20; }
        elseif ($worldOrBatch === 3) { $startMission = 21; $endMission = 25; }
        elseif ($worldOrBatch === 101) { $startMission = 1; $endMission = 5; }
        elseif ($worldOrBatch === 102) { $startMission = 6; $endMission = 10; }
        elseif ($worldOrBatch === 103) { $startMission = 11; $endMission = 15; }
        elseif ($worldOrBatch === 104) { $startMission = 16; $endMission = 20; }
        elseif ($worldOrBatch === 105) { $startMission = 21; $endMission = 25; }

        // 3. Complete Audited Mission Data Definitions (Missions 1 to 25)
        $allMissions = [
            // REALM 1: CREATION (M1 - M10)
            1 => [
                'world' => $creationWorld, 'title' => 'God Made Sky & Sun ☀️',
                'q_imgs' => ['cre_m1_sun.webp', 'cre_m1_sun.webp', 'cre_m1_sky.webp', 'cre_m1_cloud.webp'],
                'distractors' => ['cre_m1_cloud.webp', 'cre_m1_sky.webp', 'cre_m1_sun.webp', 'cre_m1_sun.webp'],
                'tf_imgs' => ['cre_m1_sun.webp', 'cre_m1_sky.webp', 'cre_m1_sun.webp', 'cre_m1_cloud.webp']
            ],
            2 => [
                'world' => $creationWorld, 'title' => 'God Made Trees & Flowers 🌸',
                'q_imgs' => ['cre_m2_flower.webp', 'cre_m2_flower.webp', 'cre_m2_tree.webp', 'cre_m2_grass.webp'],
                'distractors' => ['cre_m2_tree.webp', 'cre_m2_grass.webp', 'cre_m2_flower.webp', 'cre_m2_tree.webp'],
                'tf_imgs' => ['cre_m2_flower.webp', 'cre_m2_tree.webp', 'cre_m2_tree.webp', 'cre_m2_flower.webp']
            ],
            3 => [
                'world' => $creationWorld, 'title' => 'God Made Animals & Birds 🦁',
                'q_imgs' => ['cre_m3_lion.webp', 'cre_m3_bird.webp', 'cre_m3_fish.webp', 'cre_m3_lion.webp'],
                'distractors' => ['cre_m3_bird.webp', 'cre_m3_fish.webp', 'cre_m3_lion.webp', 'cre_m3_fish.webp'],
                'tf_imgs' => ['cre_m3_lion.webp', 'cre_m3_bird.webp', 'cre_m3_lion.webp', 'cre_m3_bird.webp']
            ],
            4 => [
                'world' => $creationWorld, 'title' => 'God Made Water & Rivers 🌊',
                'q_imgs' => ['cre_m4_river.webp', 'cre_m4_rain.webp', 'cre_m4_glass_water.webp', 'cre_m4_river.webp'],
                'distractors' => ['cre_m4_rain.webp', 'cre_m4_glass_water.webp', 'cre_m4_river.webp', 'cre_m4_rain.webp'],
                'tf_imgs' => ['cre_m4_rain.webp', 'cre_m4_rain.webp', 'cre_m4_glass_water.webp', 'cre_m4_river.webp']
            ],
            5 => [
                'world' => $creationWorld, 'title' => 'God Made Me Unique 👧👦',
                'q_imgs' => ['cre_m5_boy.webp', 'cre_m5_girl.webp', 'cre_m5_boy.webp', 'cre_m5_heart.webp'],
                'distractors' => ['cre_m5_girl.webp', 'cre_m5_boy.webp', 'cre_m5_heart.webp', 'cre_m5_girl.webp'],
                'tf_imgs' => ['cre_m5_heart.webp', 'cre_m5_boy.webp', 'cre_m5_girl.webp', 'cre_m5_boy.webp']
            ],
            6 => [
                'world' => $creationWorld, 'title' => 'God Made My Body 🖐️',
                'q_imgs' => ['cre_m6_eyes.webp', 'cre_m6_ears.webp', 'cre_m6_hands.webp', 'cre_m6_hands.webp'],
                'distractors' => ['cre_m6_ears.webp', 'cre_m6_hands.webp', 'cre_m6_eyes.webp', 'cre_m6_ears.webp'],
                'tf_imgs' => ['cre_m6_eyes.webp', 'cre_m6_ears.webp', 'cre_m6_hands.webp', 'cre_m6_hands.webp']
            ],
            7 => [
                'world' => $creationWorld, 'title' => 'God Loves My Family 👨‍👩‍👧‍👦',
                'q_imgs' => ['cre_m7_family.webp', 'cre_m7_mommy.webp', 'cre_m7_daddy.webp', 'cre_m7_family.webp'],
                'distractors' => ['cre_m7_mommy.webp', 'cre_m7_daddy.webp', 'cre_m7_family.webp', 'cre_m7_mommy.webp'],
                'tf_imgs' => ['cre_m7_family.webp', 'cre_m7_mommy.webp', 'cre_m7_family.webp', 'cre_m7_daddy.webp']
            ],
            8 => [
                'world' => $creationWorld, 'title' => 'Thanking God in Prayer 🙏',
                'q_imgs' => ['cre_m8_praying.webp', 'cre_m8_meal_pray.webp', 'cre_m8_bed_pray.webp', 'cre_m8_praying.webp'],
                'distractors' => ['cre_m8_meal_pray.webp', 'cre_m8_bed_pray.webp', 'cre_m8_praying.webp', 'cre_m8_meal_pray.webp'],
                'tf_imgs' => ['cre_m8_praying.webp', 'cre_m8_praying.webp', 'cre_m8_meal_pray.webp', 'cre_m8_bed_pray.webp']
            ],
            9 => [
                'world' => $creationWorld, 'title' => "Caring for God's Creation 🌱",
                'q_imgs' => ['cre_m9_water_plant.webp', 'cre_m9_feed_birds.webp', 'cre_m9_clean_litter.webp', 'cre_m9_water_plant.webp'],
                'distractors' => ['cre_m9_feed_birds.webp', 'cre_m9_clean_litter.webp', 'cre_m9_water_plant.webp', 'cre_m9_feed_birds.webp'],
                'tf_imgs' => ['cre_m9_water_plant.webp', 'cre_m9_feed_birds.webp', 'cre_m9_clean_litter.webp', 'cre_m9_water_plant.webp']
            ],
            10 => [
                'world' => $creationWorld, 'title' => 'Creation Realm Master 🏆',
                'q_imgs' => ['cre_m10_globe.webp', 'cre_m10_rainbow.webp', 'cre_m10_crown.webp', 'cre_m10_crown.webp'],
                'distractors' => ['cre_m10_rainbow.webp', 'cre_m10_crown.webp', 'cre_m10_globe.webp', 'cre_m10_rainbow.webp'],
                'tf_imgs' => ['cre_m10_globe.webp', 'cre_m10_crown.webp', 'cre_m10_rainbow.webp', 'cre_m10_globe.webp']
            ],

            // REALM 2: JESUS STORIES (M11 - M20)
            11 => [
                'world' => $jesusWorld, 'title' => 'Jesus Loves the Children 👧🧒',
                'q_imgs' => ['cre_m11_jesus_kids.webp', 'cre_m11_happy_child.webp', 'cre_m11_heart_love.webp', 'cre_m11_happy_child.webp'],
                'distractors' => ['cre_m11_happy_child.webp', 'cre_m11_heart_love.webp', 'cre_m11_jesus_kids.webp', 'cre_m11_heart_love.webp'],
                'tf_imgs' => ['cre_m11_jesus_kids.webp', 'cre_m11_heart_love.webp', 'cre_m11_happy_child.webp', 'cre_m11_jesus_kids.webp']
            ],
            12 => [
                'world' => $jesusWorld, 'title' => 'Baby Jesus in the Manger 👶⭐',
                'q_imgs' => ['cre_m12_manger.webp', 'cre_m12_manger.webp', 'cre_m12_star.webp', 'cre_m12_mary.webp'],
                'distractors' => ['cre_m12_mary.webp', 'cre_m12_mary.webp', 'cre_m12_mary.webp', 'cre_m12_manger.webp'],
                'tf_imgs' => ['cre_m12_manger.webp', 'cre_m12_star.webp', 'cre_m12_mary.webp', 'cre_m12_manger.webp']
            ],
            13 => [
                'world' => $jesusWorld, 'title' => 'Jesus Helps the Sick 🩺❤️',
                'q_imgs' => ['cre_m13_healing.webp', 'cre_m13_healthy_child.webp', 'cre_m13_heart.webp', 'cre_m13_healing.webp'],
                'distractors' => ['cre_m13_healthy_child.webp', 'cre_m13_heart.webp', 'cre_m13_healing.webp', 'cre_m13_heart.webp'],
                'tf_imgs' => ['cre_m13_healing.webp', 'cre_m13_heart.webp', 'cre_m13_healthy_child.webp', 'cre_m13_healing.webp']
            ],
            14 => [
                'world' => $jesusWorld, 'title' => 'Jesus Calms the Storm ⛵🌊',
                'q_imgs' => ['cre_m14_jesus_ship.webp', 'cre_m14_boat.webp', 'cre_m14_calm_water.webp', 'cre_m14_calm_water.webp'],
                'distractors' => ['cre_m14_boat.webp', 'cre_m14_calm_water.webp', 'cre_m14_jesus_ship.webp', 'cre_m14_boat.webp'],
                'tf_imgs' => ['cre_m14_calm_water.webp', 'cre_m14_jesus_ship.webp', 'cre_m14_boat.webp', 'cre_m14_calm_water.webp']
            ],
            15 => [
                'world' => $jesusWorld, 'title' => 'Jesus Feeds 5,000 🍞🐟',
                'q_imgs' => ['cre_m15_basket.webp', 'cre_m15_bread.webp', 'cre_m15_fish.webp', 'cre_m15_basket.webp'],
                'distractors' => ['cre_m15_bread.webp', 'cre_m15_fish.webp', 'cre_m15_basket.webp', 'cre_m15_fish.webp'],
                'tf_imgs' => ['cre_m15_basket.webp', 'cre_m15_bread.webp', 'cre_m15_fish.webp', 'cre_m15_basket.webp']
            ],
            16 => [
                'world' => $jesusWorld, 'title' => 'Jesus the Good Shepherd 🐑',
                'q_imgs' => ['cre_m16_shepherd.webp', 'cre_m16_lamb.webp', 'cre_m16_meadow.webp', 'cre_m16_shepherd.webp'],
                'distractors' => ['cre_m16_lamb.webp', 'cre_m16_meadow.webp', 'cre_m16_shepherd.webp', 'cre_m16_meadow.webp'],
                'tf_imgs' => ['cre_m16_shepherd.webp', 'cre_m16_lamb.webp', 'cre_m16_meadow.webp', 'cre_m16_shepherd.webp']
            ],
            17 => [
                'world' => $jesusWorld, 'title' => 'Jesus is My Kind Friend 🤝',
                'q_imgs' => ['cre_m17_friend.webp', 'cre_m17_hug.webp', 'cre_m17_handshake.webp', 'cre_m17_hug.webp'],
                'distractors' => ['cre_m17_hug.webp', 'cre_m17_handshake.webp', 'cre_m17_friend.webp', 'cre_m17_handshake.webp'],
                'tf_imgs' => ['cre_m17_friend.webp', 'cre_m17_hug.webp', 'cre_m17_handshake.webp', 'cre_m17_friend.webp']
            ],
            18 => [
                'world' => $jesusWorld, 'title' => 'Jesus Prays to Father 🙏✨',
                'q_imgs' => ['cre_m18_jesus_pray.webp', 'cre_m18_light.webp', 'cre_m18_mountain.webp', 'cre_m18_jesus_pray.webp'],
                'distractors' => ['cre_m18_light.webp', 'cre_m18_mountain.webp', 'cre_m18_jesus_pray.webp', 'cre_m18_light.webp'],
                'tf_imgs' => ['cre_m18_jesus_pray.webp', 'cre_m18_light.webp', 'cre_m18_mountain.webp', 'cre_m18_jesus_pray.webp']
            ],
            19 => [
                'world' => $jesusWorld, 'title' => 'Resurrection Joy & Easter ✝️🌸',
                'q_imgs' => ['cre_m19_tomb.webp', 'cre_m19_sunrise.webp', 'cre_m19_angel.webp', 'cre_m19_sunrise.webp'],
                'distractors' => ['cre_m19_sunrise.webp', 'cre_m19_angel.webp', 'cre_m19_tomb.webp', 'cre_m19_angel.webp'],
                'tf_imgs' => ['cre_m19_sunrise.webp', 'cre_m19_tomb.webp', 'cre_m19_angel.webp', 'cre_m19_tomb.webp']
            ],
            20 => [
                'world' => $jesusWorld, 'title' => 'Jesus Story Master 🏆',
                'q_imgs' => ['cre_m20_bible.webp', 'cre_m20_cross.webp', 'cre_m20_trophy.webp', 'cre_m20_trophy.webp'],
                'distractors' => ['cre_m20_cross.webp', 'cre_m20_trophy.webp', 'cre_m20_bible.webp', 'cre_m20_cross.webp'],
                'tf_imgs' => ['cre_m20_bible.webp', 'cre_m20_cross.webp', 'cre_m20_trophy.webp', 'cre_m20_bible.webp']
            ],

            // REALM 3: CHRISTIAN VALUES (M21 - M25)
            21 => [
                'world' => $valuesWorld, 'title' => 'Sharing Toys with Friends 🧸',
                'q_imgs' => ['cre_m21_teddy_bear.webp', 'cre_m21_blocks.webp', 'cre_m21_toy_basket.webp', 'cre_m21_toy_car.webp'],
                'distractors' => ['cre_m21_apple_core.webp', 'cre_m21_apple_core.webp', 'cre_m21_apple_core.webp', 'cre_m21_apple_core.webp'],
                'tf_imgs' => ['cre_m21_teddy_bear.webp', 'cre_m21_toy_car.webp', 'cre_m21_blocks.webp', 'cre_m21_apple_core.webp']
            ],
            22 => [
                'world' => $valuesWorld, 'title' => 'Saying Thank You & Please 😊',
                'q_imgs' => ['cre_m22_thank_you_smile.webp', 'cre_m22_please_hands.webp', 'cre_m22_flower_gift.webp', 'cre_m22_happy_box.webp'],
                'distractors' => ['cre_m22_crying_child.webp', 'cre_m22_crying_child.webp', 'cre_m22_crying_child.webp', 'cre_m22_crying_child.webp'],
                'tf_imgs' => ['cre_m22_thank_you_smile.webp', 'cre_m22_please_hands.webp', 'cre_m22_flower_gift.webp', 'cre_m22_crying_child.webp']
            ],
            23 => [
                'world' => $valuesWorld, 'title' => 'Helping Family at Home 🧹',
                'q_imgs' => ['cre_m23_broom.webp', 'cre_m23_dustpan.webp', 'cre_m23_toy_box.webp', 'cre_m23_cleaning_bucket.webp'],
                'distractors' => ['cre_m23_teddy_bear.webp', 'cre_m23_toy_car.webp', 'cre_m23_cleaning_bucket.webp', 'cre_m23_teddy_bear.webp'],
                'tf_imgs' => ['cre_m23_broom.webp', 'cre_m23_dustpan.webp', 'cre_m23_cleaning_bucket.webp', 'cre_m23_teddy_bear.webp']
            ],
            24 => [
                'world' => $valuesWorld, 'title' => 'Obeying Parents & Teachers 👂',
                'q_imgs' => ['cre_m24_sitting_quietly.webp', 'cre_m24_sitting_quietly.webp', 'cre_m24_sitting_quietly.webp', 'cre_m24_standing_on_table.webp'],
                'distractors' => ['cre_m24_screaming_child.webp', 'cre_m24_standing_on_table.webp', 'cre_m24_screaming_child.webp', 'cre_m24_raising_hand.webp'],
                'tf_imgs' => ['cre_m24_sitting_quietly.webp', 'cre_m24_raising_hand.webp', 'cre_m24_sitting_quietly.webp', 'cre_m24_standing_on_table.webp']
            ],
            25 => [
                'world' => $valuesWorld, 'title' => 'Being Honest & Truthful 💖',
                'q_imgs' => ['cre_m25_return_toy.webp', 'cre_m25_telling_truth.webp', 'cre_m25_star_badge.webp', 'cre_m25_star_box.webp'],
                'distractors' => ['cre_m25_hiding_toy.webp', 'cre_m25_hiding_toy.webp', 'cre_m25_hiding_toy.webp', 'cre_m25_hiding_toy.webp'],
                'tf_imgs' => ['cre_m25_telling_truth.webp', 'cre_m25_return_toy.webp', 'cre_m25_star_badge.webp', 'cre_m25_hiding_toy.webp']
            ],
        ];

        // 4. Seed Missions in Specified Range
        for ($mNum = $startMission; $mNum <= $endMission; $mNum++) {
            if (!isset($allMissions[$mNum])) continue;
            $mData = $allMissions[$mNum];

            $qBank = QuestionBank::firstOrCreate(
                ['name' => "Question Bank — {$mData['title']}"],
                [
                    'subject_id' => $creSubject->id,
                    'description' => "Questions for {$mData['title']}",
                ]
            );

            // 🧹 WIPE OLD PIVOT RECORDS AND OLD QUESTIONS FOR THIS MISSION BEFORE SEEDING FRESH ONES!
            DB::table('question_bank_questions')->where('question_bank_id', $qBank->id)->delete();
            QuizQuestion::where('question_bank_id', $qBank->id)->forceDelete();

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

            $this->seedQuestionsForMission($qBank, $mNum, $mData, $mcTypeId, $tfTypeId);
        }

        if ($this->command) {
            $this->command->info("🎉 Successfully seeded CRE Missions {$startMission} to {$endMission}!");
        }
    }

    private function seedQuestionsForMission($qBank, $mNum, $mData, $mcTypeId, $tfTypeId): void
    {
        for ($q = 1; $q <= 8; $q++) {
            $audioUrl = "/audio/m11/cre_m{$mNum}_q{$q}.mp3";

            // For ALL 25 Missions:
            // Q1 to Q4 = Visual Choice (Multiple Choice)
            // Q5 to Q8 = True / False (Yes / No)
            if ($q <= 4) {
                $imgFile = $mData['q_imgs'][$q - 1] ?? ($mData['q_imgs'][0] ?? "cre_m1_sun.webp");
                $disFile = $mData['distractors'][$q - 1] ?? ($mData['distractors'][0] ?? "cre_m1_cloud.webp");
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
                $imgFile = $mData['tf_imgs'][$q - 5] ?? ($mData['q_imgs'][$q - 5] ?? "cre_m1_sun.webp");
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

                // Determine if YES or NO is correct
                $isYes = ($mNum === 9 && $q === 7) ? false : (($mNum === 9 && $q === 8) ? true : ($q <= 7));
                QuestionOption::create(['question_id' => $question->id, 'text_value' => 'YES', 'is_correct' => $isYes, 'sort_order' => 1]);
                QuestionOption::create(['question_id' => $question->id, 'text_value' => 'NO', 'is_correct' => !$isYes, 'sort_order' => 2]);
            }
        }
    }
}
