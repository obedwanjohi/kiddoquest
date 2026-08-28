<?php

namespace Database\Seeders;

use App\Models\Curriculum;
use App\Models\Level;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Lesson;
use App\Models\AdventureWorld;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CBCMasterSeeder extends Seeder
{
    public function run(): void
    {
        // ════════════════════════════════════════════════════════
        // 1. CURRICULUM ROOT
        // ════════════════════════════════════════════════════════
        $curriculum = Curriculum::updateOrCreate(
            ['code' => 'CBC'],
            [
                'name' => 'Kenya CBC (Competency Based Curriculum)',
                'slug' => 'cbc',
                'description' => 'Kenya National CBC Curriculum for Early Years Education (EYE)',
                'color' => '#6366F1',
                'icon' => '🎓',
                'sort_order' => 1,
                'status' => 'published',
            ]
        );

        // ════════════════════════════════════════════════════════
        // 2. THE 3 CORE EARLY LEARNING LEVELS
        // ════════════════════════════════════════════════════════
        $levelsData = [
            [
                'name' => 'Play Group',
                'code' => 'PG',
                'stage' => 'pre_primary',
                'min_age' => 2,
                'max_age' => 3,
                'color' => '#EC4899',
                'icon' => '🧸',
                'sort_order' => 1,
            ],
            [
                'name' => 'Pre-Primary 1 (PP1)',
                'code' => 'PP1',
                'stage' => 'pre_primary',
                'min_age' => 4,
                'max_age' => 4,
                'color' => '#F59E0B',
                'icon' => '🎨',
                'sort_order' => 2,
            ],
            [
                'name' => 'Pre-Primary 2 (PP2)',
                'code' => 'PP2',
                'stage' => 'pre_primary',
                'min_age' => 5,
                'max_age' => 6,
                'color' => '#8B5CF6',
                'icon' => '🚀',
                'sort_order' => 3,
            ],
        ];

        $levels = [];
        foreach ($levelsData as $lData) {
            $levels[$lData['code']] = Level::updateOrCreate(
                ['code' => $lData['code']],
                array_merge($lData, [
                    'curriculum_id' => $curriculum->id,
                    'slug' => Str::slug($lData['name']),
                    'description' => "CBC {$lData['name']} Stage",
                    'status' => 'published',
                ])
            );
        }

        // We focus our 65 movies on the active primary learning level (PP2 / PP1)
        $targetLevel = $levels['PP2'];

        // ════════════════════════════════════════════════════════
        // 3. THE 3 SUBJECTS
        // ════════════════════════════════════════════════════════
        $subjectsData = [
            'math' => [
                'name' => 'Mathematics Activities',
                'slug' => 'mathematics',
                'description' => 'Number sense, counting, basic shapes, size comparisons, and cheerful addition.',
                'icon' => '🔢',
                'color' => '#F59E0B',
                'sort_order' => 1,
            ],
            'english' => [
                'name' => 'Language & Phonics Activities',
                'slug' => 'language-phonics',
                'description' => 'Letter sounds, vocabulary, phonics blends, listening comprehension, and speech.',
                'icon' => '📖',
                'color' => '#0284C7',
                'sort_order' => 2,
            ],
            'cre' => [
                'name' => 'Religious Education & Moral Values',
                'slug' => 'cre-values',
                'description' => 'God\'s creation, family love, respect, sharing, gratitude, and moral character.',
                'icon' => '✝️',
                'color' => '#059669',
                'sort_order' => 3,
            ],
        ];

        $subjects = [];
        foreach ($subjectsData as $key => $sData) {
            $subjects[$key] = Subject::updateOrCreate(
                ['slug' => $sData['slug']],
                array_merge($sData, [
                    'level_id' => $targetLevel->id,
                    'status' => 'published',
                ])
            );
        }

        // ════════════════════════════════════════════════════════
        // 4. TOPICS & LESSONS (Structured for the 65 Missions)
        // ════════════════════════════════════════════════════════

        // A. MATHEMATICS (20 Movie Missions Framework - Playgroup)
        $mathTopics = [
            [
                'name' => 'Counting Numbers 1 to 3 (Missions 1 to 10)',
                'lessons' => [
                    'Safari Apple Counter (1 to 3)',
                    'Banana Basket Counter',
                    'Toy Car Counter',
                    'Cute Kitten Counter',
                    'Shiny Star Counter',
                    'Red Balloon Counter',
                    'Flying Bird Counter',
                    'Yummy Cake Counter',
                    'Farm Animal Counter',
                    'Fruit Basket Grand Counter (Trophy 1 to 3)',
                ],
            ],
            [
                'name' => 'Counting Numbers 1 to 4 (Missions 11 to 15)',
                'lessons' => [
                    'School Backpack Counter (1 to 4)',
                    'Toy Box Counter',
                    'Chirping Bird Counter',
                    'Zooming Car Counter',
                    'Tall Tree Forest Counter (Trophy 1 to 4)',
                ],
            ],
            [
                'name' => 'Counting Numbers 1 to 5 (Missions 16 to 20)',
                'lessons' => [
                    'Yummy Cookie Counter (1 to 5)',
                    'Deep Forest Counter',
                    'Ocean Reef Counter',
                    'Jungle Safari Counter',
                    'Market Stall Grand Counter (Trophy 1 to 5)',
                ],
            ],
        ];
        $this->seedTopicsAndLessons($subjects['math'], $mathTopics);

        // B. ENGLISH & PHONICS (20 Movie Missions Framework)
        $engTopics = [
            [
                'name' => 'Letter Sounds & Phonics (A to J)',
                'lessons' => [
                    'Letter Sounds A and B',
                    'Letter Sounds C and D',
                    'Letter Sounds E and F',
                    'Letter Sounds G, H and I',
                    'Letter Sounds J and K',
                ],
            ],
            [
                'name' => 'Letter Sounds & Phonics (L to Z)',
                'lessons' => [
                    'Letter Sounds L, M and N',
                    'Letter Sounds O, P and Q',
                    'Letter Sounds R, S and T',
                    'Letter Sounds U, V and W',
                    'Letter Sounds X, Y and Z',
                ],
            ],
            [
                'name' => 'Everyday Words & Vocabulary',
                'lessons' => [
                    'My Family and Home Words',
                    'Classroom and School Objects',
                    'Farm and Safari Animals',
                    'Colors and Fruits Vocabulary',
                    'Action Words (Jump, Run, Sing)',
                ],
            ],
            [
                'name' => 'Listening, Rhymes & Stories',
                'lessons' => [
                    'Fun Rhyming Words',
                    'Animal Sounds Match',
                    'Leo\'s Short Safari Story',
                    'Following Friendly Instructions',
                    'Picture Story Reading',
                ],
            ],
        ];
        $this->seedTopicsAndLessons($subjects['english'], $engTopics);

        // C. CRE & MORAL VALUES (25 Movie Missions Framework)
        $creTopics = [
            [
                'name' => 'God\'s Wonderful Creation',
                'lessons' => [
                    'God Made the Sun, Moon and Stars',
                    'God Made the Rivers, Hills and Oceans',
                    'God Made Trees, Flowers and Fruits',
                    'God Made Birds and Wild Animals',
                    'God Made Fish and Sea Creatures',
                    'My Body is Fearfully and Wonderfully Made',
                    'Caring for God\'s Earth and Environment',
                ],
            ],
            [
                'name' => 'My Family & Community Gift',
                'lessons' => [
                    'Thanking God for Father and Mother',
                    'Loving My Brothers and Sisters',
                    'Respecting Grandparents and Elders',
                    'Helping with Chores at Home',
                    'Being a Good Neighbor and Friend',
                    'Showing Kindness to Visitors',
                ],
            ],
            [
                'name' => 'Living Moral Values & Good Deeds',
                'lessons' => [
                    'Sharing Toys, Food and Love',
                    'Saying "Please" and "Thank You"',
                    'Saying "I Am Sorry" and Forgiving Others',
                    'Telling the Truth Always (Honesty)',
                    'Being Patient and Waiting My Turn',
                    'Caring for Sick and Hurt Friends',
                ],
            ],
            [
                'name' => 'Prayer, Gratitude & Worship',
                'lessons' => [
                    'Talking to God in the Morning (Morning Prayer)',
                    'Saying Grace and Blessing Our Meals',
                    'Singing Praises and Joyful Songs',
                    'Bedtime Prayer and Peace at Night',
                    'Thanking God for Every Good Gift',
                    'God Is Always With Me and Loves Me',
                ],
            ],
        ];
        $this->seedTopicsAndLessons($subjects['cre'], $creTopics);

        // ════════════════════════════════════════════════════════
        // 5. THE 3 ADVENTURE WORLDS
        // ════════════════════════════════════════════════════════
        $worldsData = [
            [
                'name' => 'Whispering Forest',
                'slug' => 'whispering-forest',
                'description' => 'Where counting trees share secrets and every leaf is a math adventure!',
                'icon' => '🌲',
                'theme_color' => '#10B981',
                'subject_id' => $subjects['math']->id,
                'sort_order' => 1,
                'is_locked' => false,
            ],
            [
                'name' => 'Safari Plains',
                'slug' => 'safari-plains',
                'description' => 'Golden African savanna where letter sounds and animal friends come alive!',
                'icon' => '🦁',
                'theme_color' => '#F59E0B',
                'subject_id' => $subjects['english']->id,
                'sort_order' => 2,
                'is_locked' => false,
            ],
            [
                'name' => 'Ocean Cove',
                'slug' => 'ocean-cove',
                'description' => 'Dive into sparkling waters discovering God\'s creation, love, and moral values!',
                'icon' => '🌊',
                'theme_color' => '#0284C7',
                'subject_id' => $subjects['cre']->id,
                'sort_order' => 3,
                'is_locked' => false,
            ],
        ];

        foreach ($worldsData as $wData) {
            AdventureWorld::updateOrCreate(
                ['slug' => $wData['slug']],
                $wData
            );
        }
    }

    private function seedTopicsAndLessons(Subject $subject, array $topicsData): void
    {
        foreach ($topicsData as $tIdx => $tData) {
            $topic = Topic::updateOrCreate(
                [
                    'subject_id' => $subject->id,
                    'name' => $tData['name'],
                ],
                [
                    'slug' => Str::slug($tData['name']),
                    'description' => "Strand: {$tData['name']}",
                    'sort_order' => $tIdx + 1,
                    'status' => 'published',
                ]
            );

            foreach ($tData['lessons'] as $lIdx => $lessonTitle) {
                Lesson::updateOrCreate(
                    [
                        'topic_id' => $topic->id,
                        'title' => $lessonTitle,
                    ],
                    [
                        'slug' => Str::slug($lessonTitle),
                        'description' => "Lesson: {$lessonTitle}",
                        'sort_order' => $lIdx + 1,
                        'status' => 'published',
                        'difficulty' => 'easy',
                    ]
                );
            }
        }
    }
}
