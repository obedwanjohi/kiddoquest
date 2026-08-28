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
                'name' => 'Obed Wanjohi',
                'email' => 'obedwanjohi2019@gmail.com',
                'password' => 'Hujui@254',
                'role' => 'admin',
                'is_active' => true,
            ],
            [
                'name' => 'Obed Wanjohi',
                'email' => 'obedwanjo254@gmail.com',
                'password' => 'Hujui@254',
                'role' => 'admin',
                'is_active' => true,
            ],
            [
                'name' => 'Super Admin',
                'email' => 'admin@kiddoquest.co.ke',
                'password' => 'Hujui@254',
                'role' => 'admin',
                'is_active' => true,
            ],
        ];

        foreach ($admins as $admin) {
            Admin::updateOrCreate(
                ['email' => strtolower(trim($admin['email']))],
                $admin
            );
        }
    }
}