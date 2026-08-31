<?php

namespace Database\Seeders;

use App\Models\AdventureWorld;
use App\Models\Lesson;
use App\Models\Mission;
use App\Models\QuestionBank;
use App\Models\QuestionBankQuestion;
use App\Models\QuestionOption;
use App\Models\QuizType;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CreMissionsSeeder extends Seeder
{
    public function run(): void
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
        $mcType = QuizType::where('code', 'QT-01')->first() ?? QuizType::where('slug', 'multiple-choice')->first();
        $tfType = QuizType::where('code', 'QT-03')->first() ?? QuizType::where('slug', 'true-false')->first();
        $matchType = QuizType::where('code', 'QT-05')->first() ?? QuizType::where('slug', 'matching')->first();
        $sortType = QuizType::where('code', 'QT-04')->first() ?? QuizType::where('slug', 'drag-sort')->first();

        // 3. Define Missions 1 to 25 Data
        $missions = [
            // Realm 1: Creation (M1 - M10)
            1 => ['world' => $creationWorld, 'title' => 'God Made Sky & Sun ☀️', 'img' => 'cre_m1_sun.webp', 'sky' => 'cre_m1_sky.webp', 'cloud' => 'cre_m1_cloud.webp'],
            2 => ['world' => $creationWorld, 'title' => 'God Made Trees & Flowers 🌸', 'img' => 'cre_m2_flower.webp', 'tree' => 'cre_m2_tree.webp', 'grass' => 'cre_m2_grass.webp'],
            3 => ['world' => $creationWorld, 'title' => 'God Made Animals & Birds 🦁', 'img' => 'cre_m3_lion.webp', 'bird' => 'cre_m3_bird.webp', 'fish' => 'cre_m3_fish.webp'],
            4 => ['world' => $creationWorld, 'title' => 'God Made Water & Rivers 🌊', 'img' => 'cre_m4_river.webp', 'rain' => 'cre_m4_rain.webp', 'glass' => 'cre_m4_glass_water.webp'],
            5 => ['world' => $creationWorld, 'title' => 'God Made Me Unique 👧👦', 'img' => 'cre_m5_boy.webp', 'girl' => 'cre_m5_girl.webp', 'heart' => 'cre_m5_heart.webp'],
            6 => ['world' => $creationWorld, 'title' => 'God Made My Body 🖐️', 'img' => 'cre_m6_eyes.webp', 'ears' => 'cre_m6_ears.webp', 'hands' => 'cre_m6_hands.webp'],
            7 => ['world' => $creationWorld, 'title' => 'God Loves My Family 👨‍👩‍👧‍👦', 'img' => 'cre_m7_family.webp', 'mommy' => 'cre_m7_mommy.webp', 'daddy' => 'cre_m7_daddy.webp'],
            8 => ['world' => $creationWorld, 'title' => 'Thanking God in Prayer 🙏', 'img' => 'cre_m8_praying.webp', 'meal' => 'cre_m8_meal_pray.webp', 'bed' => 'cre_m8_bed_pray.webp'],
            9 => ['world' => $creationWorld, 'title' => "Caring for God's Creation 🌱", 'img' => 'cre_m9_water_plant.webp', 'birds' => 'cre_m9_feed_birds.webp', 'clean' => 'cre_m9_clean_litter.webp'],
            10 => ['world' => $creationWorld, 'title' => 'Creation Realm Master 🏆', 'img' => 'cre_m10_globe.webp', 'rainbow' => 'cre_m10_rainbow.webp', 'crown' => 'cre_m10_crown.webp'],

            // Realm 2: Jesus Stories (M11 - M20)
            11 => ['world' => $jesusWorld, 'title' => 'Jesus Loves the Children 👧🧒', 'img' => 'cre_m11_jesus_kids.webp', 'child' => 'cre_m11_happy_child.webp', 'love' => 'cre_m11_heart_love.webp'],
            12 => ['world' => $jesusWorld, 'title' => 'Baby Jesus in the Manger 👶⭐', 'img' => 'cre_m12_manger.webp', 'star' => 'cre_m12_star.webp', 'mary' => 'cre_m12_mary.webp'],
            13 => ['world' => $jesusWorld, 'title' => 'Jesus Helps the Sick 🩺❤️', 'img' => 'cre_m13_healing.webp', 'healthy' => 'cre_m13_healthy_child.webp', 'heart' => 'cre_m13_heart.webp'],
            14 => ['world' => $jesusWorld, 'title' => 'Jesus Calms the Storm ⛵🌊', 'img' => 'cre_m14_jesus_ship.webp', 'boat' => 'cre_m14_boat.webp', 'water' => 'cre_m14_calm_water.webp'],
            15 => ['world' => $jesusWorld, 'title' => 'Jesus Feeds 5,000 🍞🐟', 'img' => 'cre_m15_basket.webp', 'bread' => 'cre_m15_bread.webp', 'fish' => 'cre_m15_fish.webp'],
            16 => ['world' => $jesusWorld, 'title' => 'Jesus the Good Shepherd 🐑', 'img' => 'cre_m16_shepherd.webp', 'lamb' => 'cre_m16_lamb.webp', 'meadow' => 'cre_m16_meadow.webp'],
            17 => ['world' => $jesusWorld, 'title' => 'Jesus is My Kind Friend 🤝', 'img' => 'cre_m17_friend.webp', 'hug' => 'cre_m17_hug.webp', 'shake' => 'cre_m17_handshake.webp'],
            18 => ['world' => $jesusWorld, 'title' => 'Jesus Prays to Father 🙏✨', 'img' => 'cre_m18_jesus_pray.webp', 'light' => 'cre_m18_light.webp', 'hill' => 'cre_m18_mountain.webp'],
            19 => ['world' => $jesusWorld, 'title' => 'Resurrection Joy & Easter ✝️🌸', 'img' => 'cre_m19_tomb.webp', 'sun' => 'cre_m19_sunrise.webp', 'angel' => 'cre_m19_angel.webp'],
            20 => ['world' => $jesusWorld, 'title' => 'Jesus Story Master 🏆', 'img' => 'cre_m20_bible.webp', 'cross' => 'cre_m20_cross.webp', 'trophy' => 'cre_m20_trophy.webp'],

            // Realm 3: Christian Values (M21 - M25)
            21 => ['world' => $valuesWorld, 'title' => 'Sharing Toys with Friends 🧸', 'basket' => 'cre_m21_toy_basket.webp', 'trash' => 'cre_m21_trash_bin.webp', 'bear' => 'cre_m21_teddy_bear.webp', 'car' => 'cre_m21_toy_car.webp', 'blocks' => 'cre_m21_blocks.webp', 'apple' => 'cre_m21_apple_core.webp'],
            22 => ['world' => $valuesWorld, 'title' => 'Saying Thank You & Please 😊', 'happy' => 'cre_m22_happy_box.webp', 'sad' => 'cre_m22_sad_box.webp', 'smile' => 'cre_m22_thank_you_smile.webp', 'flower' => 'cre_m22_flower_gift.webp', 'please' => 'cre_m22_please_hands.webp', 'crying' => 'cre_m22_crying_child.webp'],
            23 => ['world' => $valuesWorld, 'title' => 'Helping Family at Home 🧹', 'clean' => 'cre_m23_cleaning_bucket.webp', 'toybox' => 'cre_m23_toy_box.webp', 'broom' => 'cre_m23_broom.webp', 'dustpan' => 'cre_m23_dustpan.webp', 'bear' => 'cre_m23_teddy_bear.webp', 'car' => 'cre_m23_toy_car.webp'],
            24 => ['world' => $valuesWorld, 'title' => 'Obeying Parents & Teachers 👂', 'classbox' => 'cre_m24_class_box.webp', 'stopbox' => 'cre_m24_stop_box.webp', 'quiet' => 'cre_m24_sitting_quietly.webp', 'hand' => 'cre_m24_raising_hand.webp', 'standing' => 'cre_m24_standing_on_table.webp', 'scream' => 'cre_m24_screaming_child.webp'],
            25 => ['world' => $valuesWorld, 'title' => 'Being Honest & Truthful 💖', 'starbox' => 'cre_m25_star_box.webp', 'wrongbox' => 'cre_m25_wrong_box.webp', 'return' => 'cre_m25_return_toy.webp', 'truth' => 'cre_m25_telling_truth.webp', 'badge' => 'cre_m25_star_badge.webp', 'hiding' => 'cre_m25_hiding_toy.webp'],
        ];

        // 4. Seed Missions 1 to 25
        foreach ($missions as $mNum => $mData) {
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

            $this->seedQuestionsForMission($qBank, $mNum, $mData, $mcType, $tfType, $matchType, $sortType);
        }

        $this->command->info("🎉 Successfully seeded CRE Missions 1 to 25 with 200 questions, audios, and images!");
    }

    private function seedQuestionsForMission($qBank, $mNum, $mData, $mcType, $tfType, $matchType, $sortType): void
    {
        for ($q = 1; $q <= 8; $q++) {
            $audioUrl = "/audio/m11/cre_m{$mNum}_q{$q}.mp3";

            // Determine question type & parameters based on mission batch
            if ($mNum <= 10) {
                // Missions 1 to 10: 4x Multiple Choice + 4x True/False
                if ($q <= 4) {
                    $qType = $mcType;
                    $qText = "Touch the correct picture!";
                    $imgUrl = "/images/m11/" . ($mData['img'] ?? "cre_m{$mNum}_sun.webp");
                    
                    $question = QuestionBankQuestion::create([
                        'question_bank_id' => $qBank->id,
                        'quiz_type_id' => $qType->id ?? 1,
                        'question_text' => $qText,
                        'prompt_audio' => $audioUrl,
                        'prompt_image' => $imgUrl,
                        'points' => 10,
                    ]);

                    QuestionOption::create(['question_bank_question_id' => $question->id, 'option_text' => 'Correct Choice', 'image_url' => $imgUrl, 'is_correct' => true, 'sort_order' => 1]);
                    QuestionOption::create(['question_bank_question_id' => $question->id, 'option_text' => 'Wrong Choice', 'image_url' => '/images/m11/cre_m1_cloud.webp', 'is_correct' => false, 'sort_order' => 2]);
                } else {
                    $qType = $tfType;
                    $qText = "Touch Yes or No!";
                    $imgUrl = "/images/m11/" . ($mData['img'] ?? "cre_m{$mNum}_sun.webp");

                    $question = QuestionBankQuestion::create([
                        'question_bank_id' => $qBank->id,
                        'quiz_type_id' => $qType->id ?? 1,
                        'question_text' => $qText,
                        'prompt_audio' => $audioUrl,
                        'prompt_image' => $imgUrl,
                        'points' => 10,
                    ]);

                    $isYes = ($q <= 7); // Q5, Q6, Q7 are Yes, Q8 is No
                    QuestionOption::create(['question_bank_question_id' => $question->id, 'option_text' => '🟢 YES', 'is_correct' => $isYes, 'sort_order' => 1]);
                    QuestionOption::create(['question_bank_question_id' => $question->id, 'option_text' => '🔴 NO', 'is_correct' => !$isYes, 'sort_order' => 2]);
                }
            } elseif ($mNum <= 20) {
                // Missions 11 to 20: 4x Multiple Choice + 4x Image-to-Image Matching
                if ($q <= 4) {
                    $qType = $mcType;
                    $qText = "Touch the correct picture!";
                    $imgUrl = "/images/m11/" . ($mData['img'] ?? "cre_m{$mNum}_jesus_kids.webp");

                    $question = QuestionBankQuestion::create([
                        'question_bank_id' => $qBank->id,
                        'quiz_type_id' => $qType->id ?? 1,
                        'question_text' => $qText,
                        'prompt_audio' => $audioUrl,
                        'prompt_image' => $imgUrl,
                        'points' => 10,
                    ]);

                    QuestionOption::create(['question_bank_question_id' => $question->id, 'option_text' => 'Correct Choice', 'image_url' => $imgUrl, 'is_correct' => true, 'sort_order' => 1]);
                    QuestionOption::create(['question_bank_question_id' => $question->id, 'option_text' => 'Wrong Choice', 'image_url' => '/images/m11/cre_m11_heart_love.webp', 'is_correct' => false, 'sort_order' => 2]);
                } else {
                    $qType = $matchType;
                    $qText = "Match matching pictures!";
                    $imgUrl = "/images/m11/" . ($mData['img'] ?? "cre_m{$mNum}_jesus_kids.webp");

                    $question = QuestionBankQuestion::create([
                        'question_bank_id' => $qBank->id,
                        'quiz_type_id' => $qType->id ?? 1,
                        'question_text' => $qText,
                        'prompt_audio' => $audioUrl,
                        'prompt_image' => $imgUrl,
                        'points' => 10,
                    ]);

                    QuestionOption::create(['question_bank_question_id' => $question->id, 'option_text' => 'Card 1', 'image_url' => $imgUrl, 'match_key' => 'pair_1', 'is_correct' => true, 'sort_order' => 1]);
                    QuestionOption::create(['question_bank_question_id' => $question->id, 'option_text' => 'Match 1', 'image_url' => $imgUrl, 'match_key' => 'pair_1', 'is_correct' => true, 'sort_order' => 2]);
                }
            } else {
                // Missions 21 to 25: 4x Multiple Choice + 4x Category Sorting (2 Buckets + 4 Chips)
                if ($q <= 4) {
                    $qType = $mcType;
                    $qText = "Touch the correct picture!";
                    $imgUrl = "/images/m11/" . ($mData['bear'] ?? $mData['smile'] ?? $mData['broom'] ?? $mData['quiet'] ?? $mData['return'] ?? "cre_m21_teddy_bear.webp");

                    $question = QuestionBankQuestion::create([
                        'question_bank_id' => $qBank->id,
                        'quiz_type_id' => $qType->id ?? 1,
                        'question_text' => $qText,
                        'prompt_audio' => $audioUrl,
                        'prompt_image' => $imgUrl,
                        'points' => 10,
                    ]);

                    QuestionOption::create(['question_bank_question_id' => $question->id, 'option_text' => 'Correct Choice', 'image_url' => $imgUrl, 'is_correct' => true, 'sort_order' => 1]);
                    QuestionOption::create(['question_bank_question_id' => $question->id, 'option_text' => 'Wrong Choice', 'image_url' => '/images/m11/cre_m21_toy_basket.webp', 'is_correct' => false, 'sort_order' => 2]);
                } else {
                    $qType = $sortType;
                    $qText = "Sort items into the right boxes!";
                    $imgUrl = "/images/m11/" . ($mData['bear'] ?? $mData['smile'] ?? $mData['broom'] ?? $mData['quiet'] ?? $mData['return'] ?? "cre_m21_teddy_bear.webp");

                    $question = QuestionBankQuestion::create([
                        'question_bank_id' => $qBank->id,
                        'quiz_type_id' => $qType->id ?? 1,
                        'question_text' => $qText,
                        'prompt_audio' => $audioUrl,
                        'prompt_image' => $imgUrl,
                        'metadata' => [
                            'categories' => ['Bucket 1', 'Bucket 2']
                        ],
                        'points' => 10,
                    ]);

                    QuestionOption::create(['question_bank_question_id' => $question->id, 'option_text' => 'Item 1', 'image_url' => $imgUrl, 'match_key' => 'Bucket 1', 'is_correct' => true, 'sort_order' => 1]);
                    QuestionOption::create(['question_bank_question_id' => $question->id, 'option_text' => 'Item 2', 'image_url' => $imgUrl, 'match_key' => 'Bucket 1', 'is_correct' => true, 'sort_order' => 2]);
                    QuestionOption::create(['question_bank_question_id' => $question->id, 'option_text' => 'Item 3', 'image_url' => $imgUrl, 'match_key' => 'Bucket 1', 'is_correct' => true, 'sort_order' => 3]);
                    QuestionOption::create(['question_bank_question_id' => $question->id, 'option_text' => 'Item 4', 'image_url' => '/images/m11/cre_m21_apple_core.webp', 'match_key' => 'Bucket 2', 'is_correct' => true, 'sort_order' => 4]);
                }
            }
        }
    }
}
