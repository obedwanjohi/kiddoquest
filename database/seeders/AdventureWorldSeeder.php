<?php

namespace Database\Seeders;

use App\Models\AdventureWorld;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class AdventureWorldSeeder extends Seeder
{
    public function run(): void
    {
        $mathSubject = Subject::where('slug', 'like', 'mathematics%')->first();
        $engSubject  = Subject::where('slug', 'like', 'language%')->first();
        $creSubject  = Subject::where('slug', 'like', 'cre%')->first();

        $worlds = [
            // 🔢 MATHEMATICS WORLDS (Playgroup)
            [
                'name' => 'Whispering Forest',
                'slug' => 'whispering-forest',
                'description' => 'Counting Numbers 1 to 3 with friendly apples, kittens, and shiny stars!',
                'icon' => '🌲',
                'theme_color' => '#10B981',
                'subject_id' => $mathSubject?->id,
                'sort_order' => 1,
                'is_locked' => false,
            ],
            [
                'name' => 'Sunny Meadow',
                'slug' => 'sunny-meadow',
                'description' => 'Counting Numbers 1 to 4 with backpacks, toy boxes, and zooming cars!',
                'icon' => '🎒',
                'theme_color' => '#F59E0B',
                'subject_id' => $mathSubject?->id,
                'sort_order' => 2,
                'is_locked' => false,
            ],
            [
                'name' => 'Yummy Cookie Trail',
                'slug' => 'cookie-trail',
                'description' => 'Counting Numbers 1 to 5 with sweet cookies, fruit baskets, and market friends!',
                'icon' => '🍪',
                'theme_color' => '#E11D48',
                'subject_id' => $mathSubject?->id,
                'sort_order' => 3,
                'is_locked' => false,
            ],

            // 📖 LANGUAGE & PHONICS WORLDS (Playgroup)
            [
                'name' => 'Safari Action Plains',
                'slug' => 'safari-plains',
                'description' => 'Action verbs, greetings, jambo safari and polite manners (Missions 1-10)!',
                'icon' => '🦁',
                'theme_color' => '#F59E0B',
                'subject_id' => $engSubject?->id,
                'sort_order' => 4,
                'is_locked' => false,
            ],
            [
                'name' => 'Alphabet Kingdom',
                'slug' => 'castle-of-discovery',
                'description' => 'Explore Letter Sounds A to J with animals, apples, cars and jelly (Missions 11-20)!',
                'icon' => '🏰',
                'theme_color' => '#EC4899',
                'subject_id' => $engSubject?->id,
                'sort_order' => 5,
                'is_locked' => false,
            ],

            // ✝️ CRE & MORAL VALUES WORLDS (Playgroup)
            [
                'name' => 'Ocean Cove Creation',
                'slug' => 'ocean-cove',
                'description' => 'God made the sun, stars, animals, trees, and my unique body (Missions 1-10)!',
                'icon' => '🌊',
                'theme_color' => '#0284C7',
                'subject_id' => $creSubject?->id,
                'sort_order' => 6,
                'is_locked' => false,
            ],
            [
                'name' => 'Kindness Village',
                'slug' => 'kindness-village',
                'description' => 'The life and miracles of Jesus, calming the storm and loving friends (Missions 11-20)!',
                'icon' => '🏡',
                'theme_color' => '#14B8A6',
                'subject_id' => $creSubject?->id,
                'sort_order' => 7,
                'is_locked' => false,
            ],
            [
                'name' => 'Rainbow Mountain Values',
                'slug' => 'rainbow-mountain',
                'description' => 'Sharing toys, saying thank you, helping family, and honesty (Missions 21-25)!',
                'icon' => '🌈',
                'theme_color' => '#A855F7',
                'subject_id' => $creSubject?->id,
                'sort_order' => 8,
                'is_locked' => false,
            ],
        ];

        // Wipe all old/testing worlds completely to start fresh with our 8 clean Playgroup worlds
        AdventureWorld::query()->delete();

        foreach ($worlds as $world) {
            AdventureWorld::create($world);
        }
    }
}