<?php

namespace Database\Seeders;

use App\Models\AdventureWorld;
use App\Models\Child;
use App\Models\Guardian;
use App\Models\Lesson;
use App\Models\WorldLesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuardianSeeder extends Seeder
{
    public function run(): void
    {
        // ── Clear existing ──
        WorldLesson::query()->delete();
        Child::query()->delete();
        Guardian::query()->delete();

        // ── Create Guardian (Parent) ──
        $guardian = Guardian::create([
            'name' => 'Demo Parent',
            'email' => 'parent@kidlearn.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        // ── Create a Child Profile ──
        Child::create([
            'guardian_id' => $guardian->id,
            'name' => 'Emma',
            'avatar' => 'lion',
            'favorite_color' => '#7C3AED',
            'recommended_level' => 'PP1',
            'birthdate' => '2021-06-15',
            'total_stars' => 0,
        ]);

        // ── Link Lessons to Adventure Worlds ──
        // Get all worlds (must be seeded by AdventureWorldSeeder first)
        $worlds = AdventureWorld::orderBy('sort_order')->get();
        $lessons = Lesson::orderBy('id')->get();

        if ($worlds->isEmpty() || $lessons->isEmpty()) {
            echo "⚠️  Skipping world-lesson links: worlds or lessons not found.\n";
            return;
        }

        // Distribute lessons across worlds with fun story titles
        $storyTitles = [
            'The Secret of the First Letter',
            'The Bouncing Ball Adventure',
            'The Curvy Cookie Trail',
            'Counting with Friendly Fingers',
            'The Missing Numbers Mystery',
            'The Red Strawberry Quest',
            'The Blue Sky Discovery',
            'The Noisy Farm Friends',
            'The Shape Detective',
        ];

        $worldIndex = 0;
        foreach ($lessons as $i => $lesson) {
            // Cycle through worlds so lessons are spread across all worlds
            $world = $worlds[$worldIndex % $worlds->count()];

            WorldLesson::create([
                'adventure_world_id' => $world->id,
                'lesson_id' => $lesson->id,
                'story_title' => $storyTitles[$i] ?? $lesson->title,
                'sort_order' => $i,
            ]);

            $worldIndex++;
        }

        echo "✅ Seeded: 1 guardian (parent@kidlearn.com), 1 child (Emma), and linked {$lessons->count()} lessons to worlds!\n";
    }
}