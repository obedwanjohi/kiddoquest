<?php

namespace Database\Seeders;

use App\Models\Voice;
use Illuminate\Database\Seeder;

class VoiceSeeder extends Seeder
{
    public function run(): void
    {
        $voices = [
            ['name' => 'Leo',            'gender' => 'male',    'description' => 'Friendly boy character voice', 'sort_order' => 0],
            ['name' => 'Emma',           'gender' => 'female',  'description' => 'Friendly girl character voice', 'sort_order' => 1],
            ['name' => 'Teacher Mary',   'gender' => 'female',  'description' => 'Warm teacher voice',           'sort_order' => 2],
            ['name' => 'Friendly Female', 'gender' => 'female', 'description' => 'Neutral friendly female',      'sort_order' => 3],
            ['name' => 'Friendly Male',  'gender' => 'male',    'description' => 'Neutral friendly male',        'sort_order' => 4],
        ];

        foreach ($voices as $v) {
            Voice::firstOrCreate(
                ['name' => $v['name']],
                array_merge($v, [
                    'provider' => 'browser',
                    'voice_id' => null,
                    'language' => 'en',
                    'status' => 'active',
                ])
            );
        }

        $this->command?->info('✅ Seeded ' . count($voices) . ' default voices.');
    }
}
