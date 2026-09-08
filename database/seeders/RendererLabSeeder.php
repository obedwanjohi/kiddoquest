<?php

namespace Database\Seeders;

use App\Models\AdventureWorld;
use App\Models\Mission;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\QuizQuestion;
use App\Models\QuizType;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * One mission per question type, so every renderer can be opened and played.
 *
 * The catalogue has no content at all for memory match or spot and find — the
 * website never built screens for them, so nobody ever authored any — and only
 * a handful for the rest. Testing thirteen renderers against three types of
 * real content is not testing them. This world exists to be played by a person
 * checking the app, and it is deliberately obvious about that.
 *
 * It is safe to run repeatedly: everything is keyed by slug.
 */
class RendererLabSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $subject = Subject::firstOrCreate(
                ['slug' => 'renderer-lab'],
                ['name' => 'Renderer Lab 🧪', 'code' => 'LAB', 'status' => 'published', 'sort_order' => 998]
            );

            $world = AdventureWorld::firstOrCreate(
                ['slug' => 'renderer-lab-world'],
                [
                    'subject_id'  => $subject->id,
                    'name'        => 'Question Type Lab 🧪',
                    'description' => 'One mission for each way of answering. For testing the app.',
                    'icon'        => '🧪',
                    'theme_color' => '#4338CA',
                    'sort_order'  => 998,
                ]
            );

            $order = 1;

            foreach ($this->missions() as $slug => $definition) {
                $this->buildMission($world, $subject, $slug, $definition, $order++);
            }

            $this->command?->info('🧪 Question Type Lab ready: ' . $world->missions()->count() . ' missions, one per renderer.');
        });
    }

    protected function buildMission(AdventureWorld $world, Subject $subject, string $slug, array $definition, int $order): void
    {
        $quizTypeId = QuizType::where('slug', $definition['type'])->value('id');

        $bank = QuestionBank::firstOrCreate(
            ['name' => 'Lab: ' . $definition['title']],
            ['subject_id' => $subject->id, 'quiz_type_id' => $quizTypeId, 'status' => 'published', 'difficulty' => 'easy']
        );

        $attributes = [
            'adventure_world_id'     => $world->id,
                'question_bank_id'       => $bank->id,
                'title'                  => $definition['title'],
                'description'            => $definition['blurb'],
                'intro_narration_text'   => $definition['blurb'],
                'status'                 => 'published',
                'sort_order'             => $order,
                'questions_per_session'  => 2,
                'pass_threshold_percent' => 60,
            'estimated_minutes'      => 2,
        ];

        // This schema requires a lesson on every mission, so borrow any existing one.
        if (\Illuminate\Support\Facades\Schema::hasColumn('missions', 'lesson_id')) {
            $attributes['lesson_id'] = DB::table('lessons')->value('id');
        }

        $mission = Mission::firstOrCreate(['slug' => $slug], $attributes);

        // Point an existing mission at this bank too, in case it was made before.
        if ($mission->question_bank_id !== $bank->id || $mission->adventure_world_id !== $world->id) {
            $mission->forceFill([
                'question_bank_id'   => $bank->id,
                'adventure_world_id' => $world->id,
                'status'             => 'published',
            ])->save();
        }

        if ($bank->questions()->exists()) {
            return;
        }

        foreach ($definition['questions'] as $index => $question) {
            $row = QuizQuestion::create([
                'question_bank_id' => $bank->id,
                'quiz_type_id'     => $quizTypeId,
                'type'             => $definition['type'],
                'prompt'           => $question['prompt'],
                'narration_text'   => $question['prompt'],
                'hint'             => $question['hint'] ?? null,
                'points'           => 1,
                'difficulty'       => 'easy',
                'sort_order'       => $index + 1,
                'scoring_config'   => $question['scoring_config'] ?? [],
                'metadata'         => $question['metadata'] ?? [],
            ]);

            foreach ($question['options'] ?? [] as $position => $option) {
                QuestionOption::create([
                    'question_id'  => $row->id,
                    'text_value'   => $option['text'] ?? null,
                    'is_correct'   => $option['correct'] ?? false,
                    'match_key'    => $option['key'] ?? null,
                    'content_type' => $option['side'] ?? 'text',
                    'sort_order'   => $position + 1,
                ]);
            }
        }
    }

    /**
     * @return array<string,array>
     */
    protected function missions(): array
    {
        return [
            'lab-tap-the-answer' => [
                'type' => 'multiple-choice',
                'title' => 'Tap the Answer 👆',
                'blurb' => 'Pick the right card.',
                'questions' => [
                    ['prompt' => 'Which one is a lion?', 'options' => [
                        ['text' => '🦁', 'correct' => true], ['text' => '🐟'], ['text' => '🌳'],
                    ]],
                    ['prompt' => 'Which number is three?', 'options' => [
                        ['text' => '1'], ['text' => '3', 'correct' => true], ['text' => '7'],
                    ]],
                ],
            ],
            'lab-yes-or-no' => [
                'type' => 'true-false',
                'title' => 'Yes or No 👍',
                'blurb' => 'Is it true?',
                'questions' => [
                    ['prompt' => 'A cow says moo.', 'options' => [['text' => 'True', 'correct' => true], ['text' => 'False']]],
                    ['prompt' => 'Fish live in trees.', 'options' => [['text' => 'True'], ['text' => 'False', 'correct' => true]]],
                ],
            ],
            'lab-count-them' => [
                'type' => 'count-objects',
                'title' => 'Count Them 🍎',
                'blurb' => 'How many can you see?',
                'questions' => [
                    ['prompt' => 'How many apples?', 'scoring_config' => ['count' => 3, 'emoji' => '🍎'], 'options' => [
                        ['text' => '2'], ['text' => '3', 'correct' => true], ['text' => '4'],
                    ]],
                    ['prompt' => 'How many stars?', 'scoring_config' => ['count' => 5, 'emoji' => '⭐'], 'options' => [
                        ['text' => '4'], ['text' => '5', 'correct' => true], ['text' => '6'],
                    ]],
                ],
            ],
            'lab-what-comes-next' => [
                'type' => 'complete-pattern',
                'title' => 'What Comes Next 🔷',
                'blurb' => 'Finish the pattern.',
                'questions' => [
                    ['prompt' => '🔴 🔵 🔴 🔵 … what comes next?', 'options' => [
                        ['text' => '🔴', 'correct' => true], ['text' => '🔵'], ['text' => '🟢'],
                    ]],
                    ['prompt' => '1, 2, 3, … what comes next?', 'options' => [
                        ['text' => '4', 'correct' => true], ['text' => '1'], ['text' => '9'],
                    ]],
                ],
            ],
            'lab-fill-the-gap' => [
                'type' => 'fill-blank',
                'title' => 'Fill the Gap 🔤',
                'blurb' => 'Which letter is missing?',
                'questions' => [
                    ['prompt' => 'c _ t', 'scoring_config' => ['blank_text' => 'c_t', 'target_char' => 'a'], 'options' => [
                        ['text' => 'a', 'correct' => true], ['text' => 'o'], ['text' => 'e'],
                    ]],
                    ['prompt' => 's _ n', 'scoring_config' => ['blank_text' => 's_n', 'target_char' => 'u'], 'options' => [
                        ['text' => 'i'], ['text' => 'u', 'correct' => true], ['text' => 'a'],
                    ]],
                ],
            ],
            'lab-match-the-pairs' => [
                'type' => 'matching',
                'title' => 'Match the Pairs 🔗',
                'blurb' => 'Tap one, then its partner.',
                'questions' => [
                    ['prompt' => 'Match each animal to its name.', 'options' => [
                        ['text' => '🐶', 'key' => 'dog', 'side' => 'left'],
                        ['text' => 'Dog', 'key' => 'dog', 'side' => 'right'],
                        ['text' => '🐱', 'key' => 'cat', 'side' => 'left'],
                        ['text' => 'Cat', 'key' => 'cat', 'side' => 'right'],
                    ]],
                    ['prompt' => 'Match each animal to its home.', 'options' => [
                        ['text' => '🐝', 'key' => 'hive', 'side' => 'left'],
                        ['text' => 'Hive', 'key' => 'hive', 'side' => 'right'],
                        ['text' => '🐦', 'key' => 'nest', 'side' => 'left'],
                        ['text' => 'Nest', 'key' => 'nest', 'side' => 'right'],
                    ]],
                ],
            ],
            'lab-sort-them-out' => [
                'type' => 'drag-sort',
                'title' => 'Sort Them Out 🧺',
                'blurb' => 'Put each one in its group.',
                'questions' => [
                    [
                        'prompt' => 'Sort the big and small animals.',
                        'metadata' => ['buckets' => [['name' => 'Big'], ['name' => 'Small']]],
                        'options' => [
                            ['text' => '🐘', 'key' => 'Big'],
                            ['text' => '🐜', 'key' => 'Small'],
                            ['text' => '🦒', 'key' => 'Big'],
                            ['text' => '🐭', 'key' => 'Small'],
                        ],
                    ],
                    [
                        'prompt' => 'Sort the fruit and the vehicles.',
                        'metadata' => ['buckets' => [['name' => 'Fruit'], ['name' => 'Goes']]],
                        'options' => [
                            ['text' => '🍌', 'key' => 'Fruit'],
                            ['text' => '🚗', 'key' => 'Goes'],
                            ['text' => '🍇', 'key' => 'Fruit'],
                            ['text' => '🚌', 'key' => 'Goes'],
                        ],
                    ],
                ],
            ],
            'lab-put-in-order' => [
                'type' => 'drag-sequence',
                'title' => 'Put Them in Order 🔢',
                'blurb' => 'Tap them from first to last.',
                'questions' => [
                    ['prompt' => 'Put the numbers in order.', 'options' => [
                        ['text' => '1'], ['text' => '2'], ['text' => '3'],
                    ]],
                    ['prompt' => 'Put the day in order.', 'options' => [
                        ['text' => 'Wake up'], ['text' => 'Play'], ['text' => 'Sleep'],
                    ]],
                ],
            ],
            'lab-find-the-pairs' => [
                'type' => 'memory-match',
                'title' => 'Find the Pairs 🃏',
                'blurb' => 'Turn the cards over and remember.',
                'questions' => [
                    ['prompt' => 'Find the matching pairs.', 'options' => [
                        ['text' => '☀️', 'key' => 'sun'], ['text' => '☀️', 'key' => 'sun'],
                        ['text' => '🌙', 'key' => 'moon'], ['text' => '🌙', 'key' => 'moon'],
                    ]],
                    ['prompt' => 'Find the matching pairs.', 'options' => [
                        ['text' => '🍎', 'key' => 'apple'], ['text' => '🍎', 'key' => 'apple'],
                        ['text' => '🐟', 'key' => 'fish'], ['text' => '🐟', 'key' => 'fish'],
                    ]],
                ],
            ],
            'lab-spot-and-find' => [
                'type' => 'spot-find',
                'title' => 'Spot and Find 🔍',
                'blurb' => 'Find the hidden things.',
                'questions' => [
                    [
                        'prompt' => 'Find the two hidden spots.',
                        'metadata' => ['hotspots' => [['x' => 30, 'y' => 35], ['x' => 70, 'y' => 60]], 'hotspot_radius' => 14],
                    ],
                    [
                        'prompt' => 'Find the three hidden spots.',
                        'metadata' => ['hotspots' => [['x' => 20, 'y' => 25], ['x' => 50, 'y' => 55], ['x' => 80, 'y' => 30]], 'hotspot_radius' => 14],
                    ],
                ],
            ],
            'lab-trace-it' => [
                'type' => 'tracing',
                'title' => 'Trace It ✏️',
                'blurb' => 'Draw the shape with your finger.',
                'questions' => [
                    ['prompt' => 'Trace the letter A', 'metadata' => ['character' => 'A']],
                    ['prompt' => 'Trace the number 2', 'metadata' => ['character' => '2']],
                ],
            ],
            'lab-say-it' => [
                'type' => 'speak-repeat',
                'title' => 'Say It Out Loud 🎙️',
                'blurb' => 'Read the word and say it.',
                'questions' => [
                    ['prompt' => 'Say this word', 'metadata' => ['word' => 'elephant']],
                    ['prompt' => 'Say this word', 'metadata' => ['word' => 'banana']],
                ],
            ],
            'lab-listen-and-choose' => [
                'type' => 'listen-choose',
                'title' => 'Listen and Choose 🔊',
                'blurb' => 'Listen, then pick.',
                'questions' => [
                    ['prompt' => 'Which one is the sun?', 'options' => [
                        ['text' => '☀️', 'correct' => true], ['text' => '🌧️'], ['text' => '❄️'],
                    ]],
                    ['prompt' => 'Which one is a car?', 'options' => [
                        ['text' => '🚗', 'correct' => true], ['text' => '🍎'], ['text' => '🐶'],
                    ]],
                ],
            ],
        ];
    }
}
