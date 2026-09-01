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

class SpeakRepeatWorldSeeder extends Seeder
{
    public function run(?string $tier = null): void
    {
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

        $speakType = QuizType::where('slug', 'speak-repeat')->orWhere('slug', 'speak_repeat')->orWhere('code', 'QT-07')->first()
            ?? QuizType::firstOrCreate(
                ['slug' => 'speak-repeat'],
                ['code' => 'QT-07', 'name' => 'Speak & Repeat', 'interaction_mode' => 'voice', 'has_options' => true, 'is_scoring_type' => true]
            );

        $speakTypeId = $speakType->id;

        // 🎯 2-QUESTION PROOF OF CONCEPT FOR MISSION 1 (Cat 🐱 & Cow 🐮)
        $title = "🟢 Easy — Animals (Cat & Cow) 🎙️";

        $qBank = QuestionBank::firstOrCreate(
            ['name' => "Speak & Repeat Bank — Cat & Cow"],
            [
                'subject_id' => $vocabSubject->id,
                'description' => "Speak & Repeat practice for target words Cat and Cow",
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
                'sort_order' => 1,
            ]
        );

        $mission = Mission::withTrashed()->updateOrCreate(
            ['slug' => Str::slug($title)],
            [
                'adventure_world_id' => $speakWorld->id,
                'lesson_id' => $lesson->id,
                'question_bank_id' => $qBank->id,
                'title' => $title,
                'display_title' => "Say 'Cat' 🐱 & 'Cow' 🐮",
                'description' => "Practice repeating 'Cat' and 'Cow' aloud!",
                'video_url' => "/videos/vocab_m1_intro.mp4",
                'status' => 'published',
                'sort_order' => 1,
                'deleted_at' => null,
            ]
        );

        // --- QUESTION 1: CAT ---
        $q1 = QuizQuestion::create([
            'question_bank_id' => $qBank->id,
            'quiz_type_id' => $speakTypeId,
            'prompt' => "Listen carefully and say out loud: Cat!",
            'prompt_audio_url' => "/audio/m11/speak_cat.mp3",
            'prompt_image_url' => "/images/speak/cat.jpg",
            'points' => 10,
            'sort_order' => 1,
        ]);

        QuestionOption::create([
            'question_id' => $q1->id,
            'text_value' => 'Cat',
            'image_url' => '/images/speak/cat.jpg',
            'audio_url' => '/audio/m11/speak_cat.mp3',
            'is_correct' => true,
            'sort_order' => 1,
        ]);

        // --- QUESTION 2: COW ---
        $q2 = QuizQuestion::create([
            'question_bank_id' => $qBank->id,
            'quiz_type_id' => $speakTypeId,
            'prompt' => "Listen carefully and say out loud: Cow!",
            'prompt_audio_url' => "/audio/m11/speak_cow.mp3",
            'prompt_image_url' => "/images/speak/cow.jpg",
            'points' => 10,
            'sort_order' => 2,
        ]);

        QuestionOption::create([
            'question_id' => $q2->id,
            'text_value' => 'Cow',
            'image_url' => '/images/speak/cow.jpg',
            'audio_url' => '/audio/m11/speak_cow.mp3',
            'is_correct' => true,
            'sort_order' => 1,
        ]);
    }
}
