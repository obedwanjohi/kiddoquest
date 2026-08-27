<?php

namespace Database\Seeders;

use App\Models\AdventureWorld;
use Illuminate\Database\Seeder;

class AdventureWorldSeeder extends Seeder
{
    public function run(): void
    {
        $worlds = [
            [
                'name' => 'Whispering Forest',
                'slug' => 'whispering-forest',
                'description' => 'Where the trees share secrets and every leaf is a new lesson!',
                'icon' => '🌳',
                'theme_color' => '#2D9D78',
                'sort_order' => 1,
                'is_locked' => false, // First world is always unlocked
            ],
            [
                'name' => 'Safari Plains',
                'slug' => 'safari-plains',
                'description' => 'Golden grasslands where wild friends help you learn!',
                'icon' => '🦁',
                'theme_color' => '#E8A838',
                'sort_order' => 2,
                'is_locked' => false,
            ],
            [
                'name' => 'Ocean Cove',
                'slug' => 'ocean-cove',
                'description' => 'Dive deep and discover treasures beneath the waves!',
                'icon' => '🌊',
                'theme_color' => '#3B82F6',
                'sort_order' => 3,
                'is_locked' => false,
            ],
            [
                'name' => 'Castle of Discovery',
                'slug' => 'castle-of-discovery',
                'description' => 'A magical castle where every room holds a surprise!',
                'icon' => '🏰',
                'theme_color' => '#A855F7',
                'sort_order' => 4,
                'is_locked' => false,
            ],
            [
                'name' => 'Star Valley',
                'slug' => 'star-valley',
                'description' => 'Blast off to a galaxy of numbers and letters!',
                'icon' => '🚀',
                'theme_color' => '#6366F1',
                'sort_order' => 5,
                'is_locked' => false,
            ],
        ];

        foreach ($worlds as $world) {
            AdventureWorld::updateOrCreate(
                ['slug' => $world['slug']],
                $world
            );
        }
    }
}