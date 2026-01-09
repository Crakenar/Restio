<?php

namespace Database\Seeders;

use App\Enum\UserRole;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\VacationRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create 4 companies with different sizes
        $companySizes = [
            ['users' => 25, 'requests' => 80],  // Large company
            ['users' => 18, 'requests' => 60],  // Medium company
            ['users' => 12, 'requests' => 45],  // Small company
            ['users' => 30, 'requests' => 100], // Enterprise
        ];

        foreach ($companySizes as $index => $size) {
            $this->command->info("Creating company " . ($index + 1) . "...");

            // Create company
            $company = Company::factory()->create();

            // Create company settings
            CompanySetting::factory()
                ->for($company)
                ->create();

            // Create users for this company
            $users = User::factory($size['users'])
                ->for($company)
                ->create();

            // Set roles: 1 admin, 2-3 managers, rest are employees
            $users[0]->update([
                'role' => UserRole::ADMIN,
            ]);

            $managerCount = fake()->numberBetween(2, 3);
            for ($i = 1; $i <= $managerCount; $i++) {
                $users[$i]->update(['role' => UserRole::MANAGER]);
            }

            // Create vacation requests with realistic distribution
            // 60% approved, 25% pending, 15% rejected
            $approved = (int) ($size['requests'] * 0.60);
            $pending = (int) ($size['requests'] * 0.25);
            $rejected = $size['requests'] - $approved - $pending;

            VacationRequest::factory($approved)
                ->approved()
                ->recycle($users)
                ->for($company)
                ->create();

            VacationRequest::factory($pending)
                ->pending()
                ->recycle($users)
                ->for($company)
                ->create();

            VacationRequest::factory($rejected)
                ->rejected()
                ->recycle($users)
                ->for($company)
                ->create();
        }

        // Create a test user for easy login
        $testCompany = Company::query()->first();
        $testUser = User::factory()
            ->for($testCompany)
            ->create([
                'name' => 'Test Admin',
                'email' => 'admin@example.com',
                'role' => UserRole::ADMIN,
            ]);

        // Give test user some vacation requests
        VacationRequest::factory(5)
            ->for($testUser)
            ->for($testCompany)
            ->create();

        // Output summary
        $this->command->newLine();
        $this->command->info('✅ Database seeded successfully!');
        $this->command->newLine();
        $this->command->table(
            ['Model', 'Count'],
            [
                ['Companies', Company::query()->count()],
                ['Company Settings', CompanySetting::query()->count()],
                ['Users', User::count()],
                ['Vacation Requests', VacationRequest::query()->count()],
            ]
        );

        $this->command->newLine();
        $this->command->info('🔐 Test login credentials:');
        $this->command->line('   Email: admin@example.com');
        $this->command->line('   Password: password');
    }
}
