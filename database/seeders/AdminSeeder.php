<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@kiddoquest.co.ke',
                'password' => 'admin12345',
                'role' => 'admin',
                'is_active' => true,
            ],
            [
                'name' => 'Super Admin Alt',
                'email' => 'admin@kidslearning.com',
                'password' => 'admin12345',
                'role' => 'admin',
                'is_active' => true,
            ],
            [
                'name' => 'Content Editor',
                'email' => 'editor@kidslearning.com',
                'password' => 'editor12345',
                'role' => 'editor',
                'is_active' => true,
            ],
            [
                'name' => 'Reviewer',
                'email' => 'reviewer@kidslearning.com',
                'password' => 'review12345',
                'role' => 'reviewer',
                'is_active' => true,
            ],
        ];

        foreach ($admins as $admin) {
            Admin::firstOrCreate(
                ['email' => $admin['email']],
                $admin
            );
        }
    }
}