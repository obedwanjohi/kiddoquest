<?php

namespace Database\Seeders;

use App\Models\AdventureWorld;
use App\Models\Lesson;
use App\Models\Mission;
use App\Models\QuestionBank;
use App\Models\QuizQuestion;
use App\Models\QuizType;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SpeakRepeatWorldSeeder extends Seeder
{
    public function run(?string $tier = null): void
    {
        // 1. Language & Vocabulary Subject
        $pgLevel = \App\Models\Level::where('code', 'PG')->first() 
            ?? \App\Models\Level::where('slug', 'like', '%play%')->first();

        $vocabSubject = Subject::where('slug', 'like', '%english%')
            ->orWhere('slug', 'like', '%language%')
            ->orWhere('slug', 'like', '%vocabulary%')
            ->first() ?? Subject::create([
                'name' => 'English & Vocabulary',
                'slug' => 'english-vocabulary-pg',
                'code' => 'ENG-VOCAB',
                'level_id' => $pgLevel?->id,
            ]);

        $topic = Topic::firstOrCreate(
            ['slug' => 'speak-repeat-vocabulary-mastery'],
            [
                'name' => 'Speak & Repeat Vocabulary Mastery',
                'subject_id' => $vocabSubject->id,
                'sort_order' => 1,
            ]
        );

        // 2. Speak & Repeat Safari World
        $speakWorld = AdventureWorld::updateOrCreate(
            ['slug' => 'speak-repeat-safari'],
            [
                'name' => 'Speak & Repeat Safari 🎙️',
                'description' => 'Listen carefully, tap the microphone, and repeat words aloud across Easy, Medium, and Hard difficulty levels!',
                'icon' => '🎙️',
                'theme_color' => '#3B82F6',
                'subject_id' => $vocabSubject->id,
                'sort_order' => 5,
                'is_locked' => false,
            ]
        );

        // Ensure QuizType QT-07 (Speak & Repeat) exists
        $speakType = QuizType::where('slug', 'speak-repeat')->orWhere('slug', 'speak_repeat')->orWhere('code', 'QT-07')->first()
            ?? QuizType::firstOrCreate(
                ['slug' => 'speak-repeat'],
                ['code' => 'QT-07', 'name' => 'Speak & Repeat', 'interaction_mode' => 'voice', 'has_options' => false, 'is_scoring_type' => false]
            );

        $speakTypeId = $speakType->id;

        // 3. Define 30 Vocabulary Missions across 3 Difficulty Tiers
        $vocabularyData = [
            // 🟢 TIER 1: EASY VOCABULARY (M1 - M10)
            1  => ['tier' => 'easy',   'word' => 'Cat',        'icon' => '🐱', 'desc' => '3-letter easy CVC word'],
            2  => ['tier' => 'easy',   'word' => 'Dog',        'icon' => '🐶', 'desc' => '3-letter easy CVC word'],
            3  => ['tier' => 'easy',   'word' => 'Sun',        'icon' => '☀️', 'desc' => 'Bright sun in the sky'],
            4  => ['tier' => 'easy',   'word' => 'Bus',        'icon' => '🚌', 'desc' => 'Yellow school bus'],
            5  => ['tier' => 'easy',   'word' => 'Cup',        'icon' => '☕', 'desc' => 'Warm cup of cocoa'],
            6  => ['tier' => 'easy',   'word' => 'Hat',        'icon' => '🎩', 'desc' => 'Fancy top hat'],
            7  => ['tier' => 'easy',   'word' => 'Pig',        'icon' => '🐷', 'desc' => 'Cute pink piggy'],
            8  => ['tier' => 'easy',   'word' => 'Red',        'icon' => '🔴', 'desc' => 'Primary red color'],
            9  => ['tier' => 'easy',   'word' => 'Pen',        'icon' => '🖊️', 'desc' => 'Writing pen'],
            10 => ['tier' => 'easy',   'word' => 'Box',        'icon' => '📦', 'desc' => 'Square gift box'],

            // 🟡 TIER 2: MEDIUM VOCABULARY (M11 - M20)
            11 => ['tier' => 'medium', 'word' => 'Elephant',   'icon' => '🐘', 'desc' => 'Big gentle safari animal'],
            12 => ['tier' => 'medium', 'word' => 'Butterfly',  'icon' => '🦋', 'desc' => 'Colorful fluttering butterfly'],
            13 => ['tier' => 'medium', 'word' => 'Rainbow',    'icon' => '🌈', 'desc' => 'Seven-colored rainbow'],
            14 => ['tier' => 'medium', 'word' => 'Doctor',     'icon' => '🩺', 'desc' => 'Kind helper doctor'],
            15 => ['tier' => 'medium', 'word' => 'Bicycle',    'icon' => '🚲', 'desc' => 'Two-wheeled bicycle'],
            16 => ['tier' => 'medium', 'word' => 'Triangle',   'icon' => '🔺', 'desc' => 'Three-sided geometric shape'],
            17 => ['tier' => 'medium', 'word' => 'Swimming',   'icon' => '🏊', 'desc' => 'Fun water action verb'],
            18 => ['tier' => 'medium', 'word' => 'Dancing',    'icon' => '💃', 'desc' => 'Joyful movement action verb'],
            19 => ['tier' => 'medium', 'word' => 'Yellow',     'icon' => '🟡', 'desc' => 'Bright sunny yellow color'],
            20 => ['tier' => 'medium', 'word' => 'Monkey',     'icon' => '🐒', 'desc' => 'Playful jungle monkey'],

            // 🔴 TIER 3: HARD VOCABULARY (M21 - M30)
            21 => ['tier' => 'hard',   'word' => 'Helicopter', 'icon' => '🚁', 'desc' => 'Multi-syllable flying aircraft'],
            22 => ['tier' => 'hard',   'word' => 'Watermelon', 'icon' => '🍉', 'desc' => 'Sweet juicy fruit concept'],
            23 => ['tier' => 'hard',   'word' => 'Sunflower',  'icon' => '🌻', 'desc' => 'Compound nature word'],
            24 => ['tier' => 'hard',   'word' => 'Astronaut',  'icon' => '👨‍🚀', 'desc' => 'Space explorer concept'],
            25 => ['tier' => 'hard',   'word' => 'Dinosaur',   'icon' => '🦖', 'desc' => 'Prehistoric creature concept'],
            26 => ['tier' => 'hard',   'word' => 'Caterpillar','icon' => '🐛', 'desc' => 'Multi-syllable nature concept'],
            27 => ['tier' => 'hard',   'word' => 'Firetruck',  'icon' => '🚒', 'desc' => 'Emergency helper vehicle'],
            28 => ['tier' => 'hard',   'word' => 'Pineapple',  'icon' => '🍍', 'desc' => 'Tropical fruit compound word'],
            29 => ['tier' => 'hard',   'word' => 'Strawberry', 'icon' => '🍓', 'desc' => 'Sweet red berry compound word'],
            30 => ['tier' => 'hard',   'word' => 'Umbrella',   'icon' => '☔', 'desc' => 'Rainy day protection concept'],
        ];

        // Determine range based on $tier parameter
        $start = 1;
        $end = 30;

        if ($tier === 'easy') { $start = 1; $end = 10; }
        elseif ($tier === 'medium') { $start = 11; $end = 20; }
        elseif ($tier === 'hard') { $start = 21; $end = 30; }

        for ($mNum = $start; $mNum <= $end; $mNum++) {
            if (!isset($vocabularyData[$mNum])) continue;
            $vData = $vocabularyData[$mNum];

            $tierBadge = match($vData['tier']) {
                'easy' => '🟢 Easy',
                'medium' => '🟡 Medium',
                'hard' => '🔴 Hard',
            };

            $title = "{$tierBadge} — Say '{$vData['word']}' {$vData['icon']}";

            $qBank = QuestionBank::firstOrCreate(
                ['name' => "Speak & Repeat Bank — {$vData['word']}"],
                [
                    'subject_id' => $vocabSubject->id,
                    'description' => "Speak & Repeat practice for target word {$vData['word']}",
                ]
            );

            // Wipe old questions and pivot records before seeding fresh
            DB::table('question_bank_questions')->where('question_bank_id', $qBank->id)->delete();
            QuizQuestion::where('question_bank_id', $qBank->id)->forceDelete();

            $lesson = Lesson::firstOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'topic_id' => $topic->id,
                    'title' => $title,
                    'sort_order' => $mNum,
                ]
            );

            $mission = Mission::withTrashed()->updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'adventure_world_id' => $speakWorld->id,
                    'lesson_id' => $lesson->id,
                    'question_bank_id' => $qBank->id,
                    'title' => $title,
                    'display_title' => "Say '{$vData['word']}' {$vData['icon']}",
                    'description' => $vData['desc'],
                    'video_url' => "/videos/vocab_m{$mNum}_intro.mp4",
                    'status' => 'published',
                    'sort_order' => $mNum,
                    'deleted_at' => null,
                ]
            );

            // Seed 5 Speak & Repeat questions for this word
            for ($q = 1; $q <= 5; $q++) {
                $wordLower = strtolower($vData['word']);
                $audioUrl = "/audio/m11/speak_{$wordLower}.mp3";
                $imgUrl = "/images/m11/speak_{$wordLower}.webp";

                QuizQuestion::create([
                    'question_bank_id' => $qBank->id,
                    'quiz_type_id' => $speakTypeId,
                    'prompt' => "Listen carefully and say out loud: {$vData['word']}!",
                    'prompt_audio_url' => $audioUrl,
                    'prompt_image_url' => $imgUrl,
                    'points' => 10,
                    'sort_order' => $q,
                ]);
            }
        }
    }
}
