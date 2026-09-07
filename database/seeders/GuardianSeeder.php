<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\Guardian;
use Illuminate\Database\Seeder;

class GuardianSeeder extends Seeder
{
    /**
     * Creates the demo parent + child only when they do not exist. Never deletes.
     *
     * The previous version started with Child::query()->delete() and
     * Guardian::query()->delete(), and the Docker entrypoint ran seeders on
     * every boot — a redeploy could remove every family in the database.
     */
    public function run(): void
    {
        if (app()->environment('production') && ! config('plans.seed_demo_accounts')) {
            $this->command?->warn('GuardianSeeder skipped in production (set SEED_DEMO_ACCOUNTS=true to create the demo parent).');

            return;
        }

        $guardian = Guardian::firstOrCreate(
            ['email' => 'parent@kiddoquest.co.ke'],
            [
                'name'       => 'Demo Parent',
                'password'   => '12345678', // hashed by the model cast
                'parent_pin' => config('plans.default_parent_pin', '1234'), // hashed by the model cast
                'is_active'  => true,
            ]
        );

        Child::firstOrCreate(
            ['guardian_id' => $guardian->id, 'name' => 'Emma'],
            [
                'avatar'            => 'lion',
                'favorite_color'    => 'purple',
                'recommended_level' => 'PP1',
                'birthdate'         => '2021-06-15',
                'total_stars'       => 0,
            ]
        );

        $this->command?->info('✅ Demo parent (parent@kiddoquest.co.ke / 12345678, PIN ' . config('plans.default_parent_pin', '1234') . ') and child Emma are present.');
    }
}
