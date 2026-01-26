<?php

namespace Database\Seeders;

use App\Enum\SubscriptionStatus;
use App\Enum\UserRole;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Subscription;
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
        $this->call([
            SubscriptionSeeder::class,
            AdminSeeder::class,
        ]);

        // Get subscription plans
        $subscriptions = Subscription::all();

        // Create Admin User
        // Create 4 companies with different sizes
        $companySizes = [
            ['users' => 25, 'requests' => 80],  // Large company
            ['users' => 18, 'requests' => 60],  // Medium company
            ['users' => 12, 'requests' => 45],  // Small company
            ['users' => 30, 'requests' => 100], // Enterprise
        ];

        foreach ($companySizes as $index => $size) {
            $this->command->info('Creating company '.($index + 1).'...');

            // Create company
            $company = Company::factory()->create();

            // Create company settings
            CompanySetting::factory()
                ->for($company)
                ->create();

            // Assign a random active subscription to the company
            $subscription = $subscriptions->random();
            $company->subscriptions()->create([
                'subscription_id' => $subscription->id,
                'status' => SubscriptionStatus::ACTIVE,
                'starts_at' => now(),
                'ends_at' => $subscription->interval === \App\Enum\SubscriptionInterval::ONE_TIME
                    ? null
                    : now()->add(1, $subscription->interval->value),
            ]);

            // Create teams
            $teams = collect(['Engineering', 'Marketing', 'Sales', 'HR', 'Finance'])
                ->map(fn ($name) => \App\Models\Team::create([
                    'company_id' => $company->id,
                    'name' => $name,
                ]));

            // Create users for this company
            $users = User::factory($size['users'])
                ->for($company)
                ->state(function (array $attributes) use ($teams) {
                    return [
                        'team_id' => $teams->random()->id,
                    ];
                })
                ->create();

            // Set roles: 1 owner, 1-2 admins, 2-3 managers, rest are employees
            $users[0]->update([
                'role' => UserRole::OWNER,
                'team_id' => $teams->firstWhere('name', 'Engineering')->id,
            ]);

            // Set 1-2 admins
            $adminCount = fake()->numberBetween(1, 2);
            for ($i = 1; $i <= $adminCount; $i++) {
                $users[$i]->update([
                    'role' => UserRole::ADMIN,
                ]);
            }

            // Set 2-3 managers
            $managerCount = fake()->numberBetween(2, 3);
            for ($i = $adminCount + 1; $i <= $adminCount + $managerCount; $i++) {
                $users[$i]->update([
                    'role' => UserRole::MANAGER,
                ]);
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
                'name' => 'Test Owner',
                'email' => 'owner@example.com',
                'password' => \Hash::make('password'),
                'role' => UserRole::OWNER,
                'team_id' => $testCompany->teams()->firstWhere('name', 'Engineering')->id,
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
                ['Subscriptions', Subscription::query()->count()],
                ['Companies', Company::query()->count()],
                ['Company Subscriptions', \App\Models\CompanySubscription::query()->count()],
                ['Teams', \App\Models\Team::query()->count()],
                ['Company Settings', CompanySetting::query()->count()],
                ['Users', User::count()],
                ['Vacation Requests', VacationRequest::query()->count()],
            ]
        );

        $this->command->newLine();
        $this->command->info('🔐 Test login credentials:');
        $this->command->line('   Email: owner@example.com (Owner - Can manage subscriptions)');
        $this->command->line('   Password: password');
    }
}
