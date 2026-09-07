<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Creates ONE super admin from ADMIN_EMAIL / ADMIN_PASSWORD (see .env.example).
     *
     * The previous version hard-coded three real e-mail addresses and a real password
     * in the repository. Those accounts must have their passwords changed immediately
     * — the old values remain in git history.
     */
    public function run(): void
    {
        $email = strtolower(trim((string) config('plans.admin_seed.email')));
        $password = (string) config('plans.admin_seed.password');

        if ($email === '' || $password === '') {
            if (app()->environment('production')) {
                $this->command?->warn('AdminSeeder skipped: set ADMIN_EMAIL and ADMIN_PASSWORD to seed the first admin, or use /admin/setup.');

                return;
            }

            $email = 'admin@kiddoquest.test';
            $password = Str::random(16);
            $this->command?->warn("AdminSeeder: local admin {$email} created with password: {$password}");
        }

        Admin::updateOrCreate(
            ['email' => $email],
            [
                'name'      => 'Super Admin',
                'password'  => $password, // hashed by the model cast
                'role'      => 'admin',
                'is_active' => true,
            ]
        );
    }
}
