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

        // 1. Ensure Whispering Forest World Exists
        $forestWorld = AdventureWorld::updateOrCreate(
            ['slug' => 'whispering-forest'],
            [
                'name' => 'Whispering Forest',
                'description' => 'Counting Numbers 1 to 3 with friendly apples & bananas!',
                'icon' => '🌲',
                'theme_color' => '#10B981',
                'subject_id' => $mathSubject->id,
                'sort_order' => 1,
                'is_locked' => false,
            ]
        );

        // 2. Clean up legacy test missions from World 1 so old dummy questions are wiped
        $oldMissions = Mission::withTrashed()->where('adventure_world_id', $forestWorld->id)->get();
        foreach ($oldMissions as $oldM) {
            if ($oldM->questionBank) {
                $oldM->questionBank->questions()->withTrashed()->forceDelete();
                $oldM->questionBank->forceDelete();
            }
            $oldM->forceDelete();
        }

        // 3. Define ONLY Mission 1 (Apple) & Mission 2 (Banana) for initial testing
        $missionsData = [
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

            // Find counting prompt voiceover audio (e.g. banana_count.mp3 / 1_apple.mp3)
            $promptAudioUrl = $this->findMediaUrl('audio', [
                "{$sing}_count",
                "1_{$sing}",
                "count_{$sing}",
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
