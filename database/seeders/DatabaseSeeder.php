<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            CBCMasterSeeder::class,
            GuardianSeeder::class,
            SampleMissionsSeeder::class,
            FirstLearningPathSeeder::class,
            SortQuizSeeder::class,
            MemoryMatchSeeder::class,
            SpeakRepeatSeeder::class,
            TracingSeeder::class,
        ]);
    }
}