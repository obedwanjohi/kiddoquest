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

        // ════════════════════════════════════════════════════════
        // 3. SEED 3 CORE SUBJECTS FOR ALL 3 LEVELS (PG, PP1, PP2)
        // ════════════════════════════════════════════════════════
        $subjectsTemplate = [
            'math' => [
                'name' => 'Mathematics Activities',
                'description' => 'Number sense, counting, basic shapes, size comparisons, and cheerful addition.',
                'icon' => '🔢',
                'color' => '#F59E0B',
                'sort_order' => 1,
            ],
            'english' => [
                'name' => 'Language & Phonics Activities',
                'description' => 'Listening, action verbs, greetings, letter sounds A-Z, vocabulary and rhymes.',
                'icon' => '📖',
                'color' => '#0284C7',
                'sort_order' => 2,
            ],
            'cre' => [
                'name' => 'Religious Education & Moral Values',
                'description' => 'God\'s creation, life of Jesus, family love, respect, sharing, and honesty.',
                'icon' => '✝️',
                'color' => '#059669',
                'sort_order' => 3,
            ],
        ];

        $subjects = [];
        foreach ($levels as $lvlCode => $level) {
            foreach ($subjectsTemplate as $key => $sData) {
                $slug = Str::slug($sData['name']) . '-' . strtolower($lvlCode);
                $subject = Subject::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $sData['name'],
                        'description' => $sData['description'],
                        'icon' => $sData['icon'],
                        'color' => $sData['color'],
                        'sort_order' => $sData['sort_order'],
                        'level_id' => $level->id,
                        'status' => 'published',
                    ]
                );

                // Store references for Playgroup (PG) for our first 65 missions
                if ($lvlCode === 'PG' || !isset($subjects[$key])) {
                    $subjects[$key] = $subject;
                }
            }
        }

        // ════════════════════════════════════════════════════════
        // 4. TOPICS & LESSONS (100% Synced to Playgroup CSVs)
        // ════════════════════════════════════════════════════════

        // A. MATHEMATICS (20 Playgroup Missions)
        $mathTopics = [
            [
                'name' => 'Counting Numbers 1 to 3 (Missions 1 to 10)',
                'lessons' => [
                    'Safari Apple Counter (1 to 3)',
                    'Banana Basket Counter (1 to 3)',
                    'Toy Car Counter (1 to 3)',
                    'Cute Kitten Counter (1 to 3)',
                    'Shiny Star Counter (1 to 3)',
                    'Red Balloon Counter (1 to 3)',
                    'Flying Bird Counter (1 to 3)',
                    'Yummy Cake Counter (1 to 3)',
                    'Farm Animal Counter (1 to 3)',
                    'Fruit Basket Grand Counter (Trophy 1 to 3)',
                ],
            ],
            [
                'name' => 'Counting Numbers 1 to 4 (Missions 11 to 15)',
                'lessons' => [
                    'School Backpack Counter (1 to 4)',
                    'Toy Box Counter (1 to 4)',
                    'Chirping Bird Counter (1 to 4)',
                    'Zooming Car Counter (1 to 4)',
                    'Tall Tree Forest Counter (Trophy 1 to 4)',
                ],
            ],
            [
                'name' => 'Counting Numbers 1 to 5 (Missions 16 to 20)',
                'lessons' => [
                    'Yummy Cookie Counter (1 to 5)',
                    'Deep Forest Counter (1 to 5)',
                    'Ocean Reef Counter (1 to 5)',
                    'Jungle Safari Counter (1 to 5)',
                    'Market Stall Grand Counter (Trophy 1 to 5)',
                ],
            ],
        ];
        $this->seedTopicsAndLessons($subjects['math'], $mathTopics);

        // B. ENGLISH & PHONICS (20 Playgroup Missions)
        $engTopics = [
            [
                'name' => 'Listening & Action Verbs (Missions 1 to 6)',
                'lessons' => [
                    'Touch the Ball',
                    'Clap Your Hands',
                    'Wave Hello',
                    'Point to the Sun',
                    'Stand Up, Sit Down',
                    'Open and Close',
                ],
            ],
            [
                'name' => 'Greetings & Polite Manners (Missions 7 to 10)',
                'lessons' => [
                    'Greetings Safari Jambo',
                    'Saying Good Morning',
                    'Saying Thank You',
                    'Saying Goodbye',
                ],
            ],
            [
                'name' => 'Letter Sounds A to J (Missions 11 to 20)',
                'lessons' => [
                    'Letter A (Apple & Ant)',
                    'Letter B (Ball & Bear)',
                    'Letter C (Cat & Car)',
                    'Letter D (Dog & Duck)',
                    'Letter E (Egg & Elephant)',
                    'Letter F (Fish & Frog)',
                    'Letter G (Giraffe & Goat)',
                    'Letter H (Hat & House)',
                    'Letter I (Igloo & Insect)',
                    'Letter J (Jug & Jelly)',
                ],
            ],
        ];
        $this->seedTopicsAndLessons($subjects['english'], $engTopics);

        // C. CRE & MORAL VALUES (25 Playgroup Missions)
        $creTopics = [
            [
                'name' => 'God\'s Wonderful Creation (Missions 1 to 10)',
                'lessons' => [
                    'God Made the Sky and Sun',
                    'God Made Trees and Flowers',
                    'God Made Animals and Birds',
                    'God Made Water and Rivers',
                    'God Made Me Unique',
                    'God Made My Body',
                    'God Loves My Family',
                    'Thanking God in Prayer',
                    'Caring for God\'s Creation',
                    'Creation Realm Grand Master (Trophy)',
                ],
            ],
            [
                'name' => 'Life of Jesus & Miracles (Missions 11 to 20)',
                'lessons' => [
                    'Jesus Loves the Children',
                    'Baby Jesus in the Manger',
                    'Jesus Helps the Sick',
                    'Jesus Calms the Storm',
                    'Jesus Feeds 5,000',
                    'Jesus the Good Shepherd',
                    'Jesus Our Kind Friend',
                    'Jesus Prays to the Father',
                    'Jesus Resurrection Joy',
                    'Jesus Story Grand Master (Trophy)',
                ],
            ],
            [
                'name' => 'Christian Values & Kindness (Missions 21 to 25)',
                'lessons' => [
                    'Sharing Toys with Friends',
                    'Saying "Thank You" and "Please"',
                    'Helping Family at Home',
                    'Obeying Parents and Teachers',
                    'Being Honest and Truthful',
                ],
            ],
        ];
        $this->seedTopicsAndLessons($subjects['cre'], $creTopics);

        // ════════════════════════════════════════════════════════
        // 5. THE 8 ADVENTURE WORLDS (Exact Subject & CSV Matching)
        // ════════════════════════════════════════════════════════
        $worldsData = [
            // MATHEMATICS WORLDS 🔢
            [
                'name' => 'Whispering Forest',
                'slug' => 'whispering-forest',
                'description' => 'Counting Numbers 1 to 3 with friendly apples, kittens, and shiny stars!',
                'icon' => '🌲',
                'theme_color' => '#10B981',
                'subject_id' => $subjects['math']->id,
                'sort_order' => 1,
                'is_locked' => false,
            ],
            [
                'name' => 'Sunny Meadow',
                'slug' => 'sunny-meadow',
                'description' => 'Counting Numbers 1 to 4 with backpacks, toy boxes, and zooming cars!',
                'icon' => '🎒',
                'theme_color' => '#F59E0B',
                'subject_id' => $subjects['math']->id,
                'sort_order' => 2,
                'is_locked' => false,
            ],
            [
                'name' => 'Yummy Cookie Trail',
                'slug' => 'cookie-trail',
                'description' => 'Counting Numbers 1 to 5 with sweet cookies, fruit baskets, and market friends!',
                'icon' => '🍪',
                'theme_color' => '#E11D48',
                'subject_id' => $subjects['math']->id,
                'sort_order' => 3,
                'is_locked' => false,
            ],

            // LANGUAGE & PHONICS WORLDS 📖
            [
                'name' => 'Safari Action Plains',
                'slug' => 'safari-plains',
                'description' => 'Action verbs, greetings, jambo safari and polite manners (Missions 1-10)!',
                'icon' => '🦁',
                'theme_color' => '#F59E0B',
                'subject_id' => $subjects['english']->id,
                'sort_order' => 4,
                'is_locked' => false,
            ],
            [
                'name' => 'Alphabet Kingdom',
                'slug' => 'castle-of-discovery',
                'description' => 'Explore Letter Sounds A to J with animals, apples, cars and jelly (Missions 11-20)!',
                'icon' => '🏰',
                'theme_color' => '#EC4899',
                'subject_id' => $subjects['english']->id,
                'sort_order' => 5,
                'is_locked' => false,
            ],

            // CRE & MORAL VALUES WORLDS ✝️
            [
                'name' => 'Ocean Cove Creation',
                'slug' => 'ocean-cove',
                'description' => 'God made the sun, stars, animals, trees, and my unique body (Missions 1-10)!',
                'icon' => '🌊',
                'theme_color' => '#0284C7',
                'subject_id' => $subjects['cre']->id,
                'sort_order' => 6,
                'is_locked' => false,
            ],
            [
                'name' => 'Kindness Village',
                'slug' => 'kindness-village',
                'description' => 'The life and miracles of Jesus, calming the storm and loving friends (Missions 11-20)!',
                'icon' => '🏡',
                'theme_color' => '#14B8A6',
                'subject_id' => $subjects['cre']->id,
                'sort_order' => 7,
                'is_locked' => false,
            ],
            [
                'name' => 'Rainbow Mountain Values',
                'slug' => 'rainbow-mountain',
                'description' => 'Sharing toys, saying thank you, helping family, and honesty (Missions 21-25)!',
                'icon' => '🌈',
                'theme_color' => '#A855F7',
                'subject_id' => $subjects['cre']->id,
                'sort_order' => 8,
                'is_locked' => false,
            ],
        ];

        // Wipe all old/testing worlds completely to start fresh with our 8 clean Playgroup worlds
        AdventureWorld::query()->delete();

        foreach ($worldsData as $wData) {
            AdventureWorld::create($wData);
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
