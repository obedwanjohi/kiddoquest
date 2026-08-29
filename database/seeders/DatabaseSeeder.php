<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            QuizTypeSeeder::class,
            CBCMasterSeeder::class,
            AdventureWorldSeeder::class,
            GuardianSeeder::class,
            SampleMissionsSeeder::class,
            PlaygroupMathSeeder::class,
            FirstLearningPathSeeder::class,
            SortQuizSeeder::class,
            MemoryMatchSeeder::class,
            SpeakRepeatSeeder::class,
            TracingSeeder::class,
        ]);
    }
}