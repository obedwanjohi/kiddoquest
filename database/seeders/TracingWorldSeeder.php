<?php

namespace Database\Seeders;

use App\Models\AdventureWorld;
use App\Models\Lesson;
use App\Models\Media;
use App\Models\Mission;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\QuizQuestion;
use App\Models\QuizType;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TracingWorldSeeder extends Seeder
{
    protected ?\Illuminate\Support\Collection $cachedMedia = null;

    public function run(?int $targetWorld = null): void
    {
        $tracingSubject = Subject::where('slug', 'like', '%tracing%')->first()
            ?? Subject::firstOrCreate(
                ['slug' => 'tracing-writing'],
                ['name' => 'Tracing & Pre-Writing', 'code' => 'TRACE']
            );

        $topic = Topic::firstOrCreate(
            ['slug' => 'pre-writing-letter-number-tracing'],
            [
                'name' => 'Pre-Writing & Tracing Mastery',
                'subject_id' => $tracingSubject->id,
                'sort_order' => 1,
            ]
        );

        // 1. Ensure 3 Tracing Worlds Exist
        $lineWorld = AdventureWorld::updateOrCreate(
            ['slug' => 'line-tracing-trail'],
            [
                'name' => 'Line & Pattern Trail ✏️',
                'description' => 'Pre-writing motor skills with straight lines, curves, loops, and zig-zags!',
                'icon' => '✏️',
                'theme_color' => '#8B5CF6',
                'subject_id' => $tracingSubject->id,
                'sort_order' => 10,
                'is_locked' => false,
            ]
        );

        $letterWorld = AdventureWorld::updateOrCreate(
            ['slug' => 'letter-tracing-safari'],
            [
                'name' => 'Alphabet Letter Safari 🔤',
                'description' => 'Trace uppercase A-Z and lowercase a-z with animated stroke guides!',
                'icon' => '🔤',
                'theme_color' => '#3B82F6',
                'subject_id' => $tracingSubject->id,
                'sort_order' => 11,
                'is_locked' => false,
            ]
        );

        $numberWorld = AdventureWorld::updateOrCreate(
            ['slug' => 'number-tracing-kingdom'],
            [
                'name' => 'Number Tracing Kingdom 🔢',
                'description' => 'Trace numbers 0 through 10 with step-by-step arrows and counts!',
                'icon' => '🔢',
                'theme_color' => '#F59E0B',
                'subject_id' => $tracingSubject->id,
                'sort_order' => 12,
                'is_locked' => false,
            ]
        );

        $targetWorldIds = match ($targetWorld) {
            1 => [$lineWorld->id],
            2 => [$letterWorld->id],
            3 => [$numberWorld->id],
            default => [$lineWorld->id, $letterWorld->id, $numberWorld->id],
        };

        // 2. Fast Bulk Cleanup for target world(s)
        $missionIds  = \DB::table('missions')->whereIn('adventure_world_id', $targetWorldIds)->pluck('id');
        $qBankIds    = \DB::table('question_banks')->whereIn('id', \DB::table('missions')->whereIn('adventure_world_id', $targetWorldIds)->pluck('question_bank_id'))->pluck('id');
        $questionIds = \DB::table('quiz_questions')->whereIn('question_bank_id', $qBankIds)->pluck('id');

        \DB::table('question_options')->whereIn('question_id', $questionIds)->delete();
        \DB::table('quiz_questions')->whereIn('id', $questionIds)->delete();
        \DB::table('question_banks')->whereIn('id', $qBankIds)->delete();
        \DB::table('missions')->whereIn('id', $missionIds)->delete();

        // Resolve QT-12 Tracing QuizType
        $tracingTypeId = QuizType::where('code', 'QT-12')->orWhere('slug', 'tracing')->value('id') ?? 12;

        // 3. Define Missions for Each Tracing World
        $missionsData = [];

        // ── WORLD 1: Line & Pattern Trail (Pre-writing lines) ──
        $lines = [
            ['title' => 'Straight Lines Down ✏️', 'char' => '|', 'prompt' => 'Trace straight lines from top to bottom! Down, down, down!'],
            ['title' => 'Horizontal Lines Across ↔️', 'char' => '-', 'prompt' => 'Trace horizontal lines smoothly from left to right!'],
            ['title' => 'Slanted Rain Lines 🌧️', 'char' => '/', 'prompt' => 'Trace slanted rain lines falling down!'],
            ['title' => 'Curved Wave Lines 🌊', 'char' => '~', 'prompt' => 'Trace smooth ocean waves up and down!'],
            ['title' => 'Continuous Cursive Loops ➰', 'char' => 'O', 'prompt' => 'Trace continuous loops around and around!'],
        ];
        foreach ($lines as $idx => $line) {
            $missionsData[] = [
                'world' => $lineWorld,
                'num' => $idx + 1,
                'title' => $line['title'],
                'char' => $line['char'],
                'prompt' => $line['prompt'],
                'type' => 'Line',
            ];
        }

        // ── WORLD 2: Alphabet Letter Safari (A-Z) ──
        $letters = range('A', 'Z');
        foreach ($letters as $idx => $letter) {
            $lower = strtolower($letter);
            $missionsData[] = [
                'world' => $letterWorld,
                'num' => $idx + 1,
                'title' => "Trace Letter {$letter} {$lower} 🔤",
                'char' => $letter,
                'lower' => $lower,
                'prompt' => "Trace uppercase {$letter} and lowercase {$lower} with smooth lines!",
                'type' => 'Letter',
            ];
        }

        // ── WORLD 3: Number Tracing Kingdom (0-10) ──
        foreach (range(0, 10) as $num) {
            $missionsData[] = [
                'world' => $numberWorld,
                'num' => $num + 1,
                'title' => "Trace Number {$num} 🔢",
                'char' => (string) $num,
                'prompt' => "Trace number {$num} step-by-step on screen!",
                'type' => 'Number',
            ];
        }

        // 4. Process & Create Tracing Missions
        foreach ($missionsData as $mData) {
            if ($targetWorld && !in_array($mData['world']->id, $targetWorldIds)) {
                continue;
            }

            $mNum = $mData['num'];
            $char = $mData['char'];

            // Find video if available
            $videoUrl = $this->findMediaUrl('video', ["Trace {$char}", "Tracing {$char}", "Letter {$char}"]);

            // Create Question Bank
            $qBank = QuestionBank::create([
                'name'        => "Question Bank — {$mData['title']}",
                'subject_id'  => $tracingSubject->id,
                'description' => "Tracing questions for {$mData['title']}",
            ]);

            // Find or create Lesson
            $lesson = Lesson::firstOrCreate(
                ['slug' => Str::slug($mData['title'])],
                [
                    'topic_id'   => $topic->id,
                    'title'      => $mData['title'],
                    'sort_order' => $mNum,
                ]
            );

            // Create Mission
            $mission = Mission::withTrashed()->updateOrCreate(
                ['slug' => Str::slug($mData['title'])],
                [
                    'adventure_world_id'     => $mData['world']->id,
                    'lesson_id'              => $lesson->id,
                    'question_bank_id'       => $qBank->id,
                    'title'                  => $mData['title'],
                    'display_title'          => $mData['title'],
                    'description'            => $mData['prompt'],
                    'video_url'              => $videoUrl,
                    'status'                 => 'published',
                    'sort_order'             => $mNum,
                    'pass_threshold_percent' => 0, // Practice only (0% threshold = pass automatically!)
                    'stars_reward'           => 3,
                ]
            );
            if ($mission->trashed()) {
                $mission->restore();
            }

            // Question 1: Main Character Tracing Question
            $q = QuizQuestion::create([
                'question_bank_id' => $qBank->id,
                'quiz_type_id'     => $tracingTypeId,
                'prompt'           => "✏️ {$mData['prompt']}",
                'hint'             => "Follow the dashed lines and arrows with your finger!",
                'explanation'      => "Awesome tracing! You are writing like a star! 🌟",
                'points'           => 1,
                'sort_order'       => 1,
                'metadata'         => [
                    'character' => $char,
                    'traceType' => $mData['type'],
                    'practice'  => true,
                ],
            ]);

            QuestionOption::create([
                'question_id' => $q->id,
                'text_value'  => $char,
                'is_correct'  => true,
                'sort_order'  => 1,
            ]);

            // If it's a letter, add lowercase tracing question as Question 2
            if (isset($mData['lower'])) {
                $q2 = QuizQuestion::create([
                    'question_bank_id' => $qBank->id,
                    'quiz_type_id'     => $tracingTypeId,
                    'prompt'           => "✏️ Now trace lowercase letter {$mData['lower']}!",
                    'hint'             => "Follow the dashed lines smoothly!",
                    'explanation'      => "Great job tracing lowercase {$mData['lower']}! 🌟",
                    'points'           => 1,
                    'sort_order'       => 2,
                    'metadata'         => [
                        'character' => $mData['lower'],
                        'traceType' => 'Letter',
                        'case'      => 'lowercase',
                        'practice'  => true,
                    ],
                ]);

                QuestionOption::create([
                    'question_id' => $q2->id,
                    'text_value'  => $mData['lower'],
                    'is_correct'  => true,
                    'sort_order'  => 1,
                ]);
            }
        }
    }

    protected function findMediaUrl(string $type, array $keywords): ?string
    {
        if ($this->cachedMedia === null) {
            $this->cachedMedia = Media::all();
        }

        foreach ($keywords as $kw) {
            $found = $this->cachedMedia->first(function ($media) use ($type, $kw) {
                $typeMatch = str_contains(strtolower($media->type ?? ''), strtolower($type));
                if (!$typeMatch) return false;

                $kwLower = strtolower($kw);
                return str_contains(strtolower($media->name ?? ''), $kwLower) ||
                       str_contains(strtolower($media->file_name ?? ''), $kwLower) ||
                       str_contains(strtolower($media->file_path ?? ''), $kwLower);
            });

            if ($found) {
                return $found->url;
            }
        }

        return null;
    }
}
