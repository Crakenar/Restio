<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Admin::updateOrCreate(
            ['email' => 'admin@restio.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'is_super_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin created: admin@restio.com / password');
    }
}
