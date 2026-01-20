<?php

namespace Database\Seeders;

use App\Enum\UserRole;
use App\Enum\VacationRequestStatus;
use App\Enum\VacationRequestType;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ComprehensiveTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder creates a comprehensive test dataset with:
     * - 3 companies (small, medium, large)
     * - Multiple teams per company
     * - Various user roles
     * - Historical vacation requests with different statuses
     * - Realistic data for load testing and UAT
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting comprehensive test data seeding...');

        // Create subscriptions if they don't exist
        $this->createSubscriptions();

        // Create 3 test companies
        $smallCompany = $this->createSmallCompany();
        $mediumCompany = $this->createMediumCompany();
        $largeCompany = $this->createLargeCompany();

        $this->command->info('✅ Test data seeding completed!');
        $this->printSummary($smallCompany, $mediumCompany, $largeCompany);
    }

    /**
     * Create subscription plans
     */
    protected function createSubscriptions(): void
    {
        if (Subscription::count() === 0) {
            Subscription::create(['slug' => 'monthly', 'name' => 'Monthly Plan', 'price' => 29.99]);
            Subscription::create(['slug' => 'yearly', 'name' => 'Yearly Plan', 'price' => 299.99]);
            $this->command->info('Created subscription plans');
        }
    }

    /**
     * Create a small company (1-10 employees)
     */
    protected function createSmallCompany(): Company
    {
        $this->command->info('Creating Small Company...');

        $company = Company::factory()->create(['name' => 'Small Startup Inc']);
        CompanySetting::factory()->create([
            'company_id' => $company->id,
            'annual_days' => 20,
        ]);

        // Create 1 team
        $team = Team::factory()->create([
            'company_id' => $company->id,
            'name' => 'Main Team',
        ]);

        // Create 1 owner
        $this->createUser($company, $team, UserRole::OWNER, 'owner@smallstartup.com', 'Owner Smith');

        // Create 1 admin
        $this->createUser($company, $team, UserRole::ADMIN, 'admin@smallstartup.com', 'Admin Johnson');

        // Create 1 manager
        $this->createUser($company, $team, UserRole::MANAGER, 'manager@smallstartup.com', 'Manager Davis');

        // Create 7 employees
        for ($i = 1; $i <= 7; $i++) {
            $user = $this->createUser($company, $team, UserRole::EMPLOYEE, "employee{$i}@smallstartup.com", "Employee {$i}");
            $this->createVacationRequests($user, $company, 3);
        }

        $this->command->info("✓ Small Company created with {$company->users()->count()} users");

        return $company;
    }

    /**
     * Create a medium company (50-100 employees)
     */
    protected function createMediumCompany(): Company
    {
        $this->command->info('Creating Medium Company...');

        $company = Company::factory()->create(['name' => 'Medium Business Corp']);
        CompanySetting::factory()->create([
            'company_id' => $company->id,
            'annual_days' => 25,
        ]);

        // Create 5 teams
        $teams = [];
        foreach (['Engineering', 'Sales', 'Marketing', 'Support', 'Operations'] as $teamName) {
            $teams[] = Team::factory()->create([
                'company_id' => $company->id,
                'name' => $teamName,
            ]);
        }

        // Create 1 owner
        $this->createUser($company, $teams[0], UserRole::OWNER, 'owner@mediumbusiness.com', 'CEO Williams');

        // Create 2 admins
        $this->createUser($company, $teams[0], UserRole::ADMIN, 'admin1@mediumbusiness.com', 'HR Director');
        $this->createUser($company, $teams[0], UserRole::ADMIN, 'admin2@mediumbusiness.com', 'Operations Director');

        // Create 1 manager per team
        foreach ($teams as $index => $team) {
            $this->createUser($company, $team, UserRole::MANAGER, "manager{$index}@mediumbusiness.com", "{$team->name} Manager");
        }

        // Create 10 employees per team
        foreach ($teams as $teamIndex => $team) {
            for ($i = 1; $i <= 10; $i++) {
                $user = $this->createUser($company, $team, UserRole::EMPLOYEE, "emp{$teamIndex}{$i}@mediumbusiness.com", "{$team->name} Employee {$i}");
                $this->createVacationRequests($user, $company, rand(4, 8));
            }
        }

        $this->command->info("✓ Medium Company created with {$company->users()->count()} users");

        return $company;
    }

    /**
     * Create a large company (100+ employees)
     */
    protected function createLargeCompany(): Company
    {
        $this->command->info('Creating Large Company...');

        $company = Company::factory()->create(['name' => 'Large Enterprise LLC']);
        CompanySetting::factory()->create([
            'company_id' => $company->id,
            'annual_days' => 30,
        ]);

        // Create 10 teams
        $teamNames = [
            'Engineering',
            'Frontend Development',
            'Backend Development',
            'QA & Testing',
            'Sales',
            'Marketing',
            'Customer Support',
            'Operations',
            'Finance',
            'HR',
        ];

        $teams = [];
        foreach ($teamNames as $teamName) {
            $teams[] = Team::factory()->create([
                'company_id' => $company->id,
                'name' => $teamName,
            ]);
        }

        // Create 1 owner
        $this->createUser($company, $teams[0], UserRole::OWNER, 'ceo@largeenterprise.com', 'CEO Anderson');

        // Create 3 admins
        $this->createUser($company, $teams[0], UserRole::ADMIN, 'cto@largeenterprise.com', 'CTO Brown');
        $this->createUser($company, $teams[8], UserRole::ADMIN, 'cfo@largeenterprise.com', 'CFO Martinez');
        $this->createUser($company, $teams[9], UserRole::ADMIN, 'chro@largeenterprise.com', 'CHRO Garcia');

        // Create 2 managers per team
        foreach ($teams as $teamIndex => $team) {
            $this->createUser($company, $team, UserRole::MANAGER, "manager1-{$teamIndex}@largeenterprise.com", "{$team->name} Manager 1");
            $this->createUser($company, $team, UserRole::MANAGER, "manager2-{$teamIndex}@largeenterprise.com", "{$team->name} Manager 2");
        }

        // Create 15 employees per team
        foreach ($teams as $teamIndex => $team) {
            for ($i = 1; $i <= 15; $i++) {
                $user = $this->createUser($company, $team, UserRole::EMPLOYEE, "e{$teamIndex}-{$i}@largeenterprise.com", "{$team->name} Employee {$i}");
                $this->createVacationRequests($user, $company, rand(5, 12));
            }
        }

        $this->command->info("✓ Large Company created with {$company->users()->count()} users");

        return $company;
    }

    /**
     * Create a user with consistent password
     */
    protected function createUser(Company $company, Team $team, UserRole $role, string $email, string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'), // All test users use 'password'
            'company_id' => $company->id,
            'team_id' => $team->id,
            'role' => $role->value,
        ]);
    }

    /**
     * Create vacation requests for a user with realistic data
     */
    protected function createVacationRequests(User $user, Company $company, int $count): void
    {
        $types = [
            VacationRequestType::VACATION,
            VacationRequestType::SICK,
            VacationRequestType::PERSONAL,
            VacationRequestType::WFH,
        ];

        $statuses = [
            VacationRequestStatus::APPROVED,
            VacationRequestStatus::REJECTED,
            VacationRequestStatus::PENDING,
        ];

        for ($i = 0; $i < $count; $i++) {
            $type = $types[array_rand($types)];
            $status = $statuses[array_rand($statuses)];

            // Create requests spread across past and future
            $isHistorical = $i < ($count - 2); // Last 2 are future/pending
            $daysOffset = $isHistorical ? -rand(30, 365) : rand(7, 90);
            $duration = rand(1, 10);

            $startDate = now()->addDays($daysOffset);
            $endDate = $startDate->copy()->addDays($duration);

            $request = VacationRequest::factory()->create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'type' => $type,
                'status' => $status,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'reason' => $this->getReasonForType($type),
            ]);

            // Add approval/rejection data for non-pending requests
            if ($status === VacationRequestStatus::APPROVED) {
                $manager = $company->users()->where('role', UserRole::MANAGER->value)->inRandomOrder()->first();
                $request->update([
                    'approved_by' => $manager?->id,
                    'approved_date' => $startDate->copy()->subDays(rand(1, 7)),
                ]);
            } elseif ($status === VacationRequestStatus::REJECTED) {
                $manager = $company->users()->where('role', UserRole::MANAGER->value)->inRandomOrder()->first();
                $request->update([
                    'approved_by' => $manager?->id,
                    'rejection_reason' => $this->getRejectionReason(),
                ]);
            }
        }
    }

    /**
     * Get a realistic reason for a vacation type
     */
    protected function getReasonForType(VacationRequestType $type): string
    {
        $reasons = [
            VacationRequestType::VACATION->value => [
                'Family vacation',
                'Beach holiday',
                'Visit relatives',
                'Anniversary trip',
                'Mountain retreat',
            ],
            VacationRequestType::SICK->value => [
                'Medical appointment',
                'Flu recovery',
                'Doctor visit',
                'Health checkup',
                'Recovery time',
            ],
            VacationRequestType::PERSONAL->value => [
                'Personal matters',
                'Family event',
                'Moving house',
                'Child care',
                'Personal appointment',
            ],
            VacationRequestType::WFH->value => [
                'Remote work',
                'Home office day',
                'Flexible work arrangement',
                'Work from home',
                'Remote setup',
            ],
        ];

        $typeReasons = $reasons[$type->value] ?? ['Time off'];

        return $typeReasons[array_rand($typeReasons)];
    }

    /**
     * Get a realistic rejection reason
     */
    protected function getRejectionReason(): string
    {
        $reasons = [
            'Conflicting project deadlines',
            'Team at capacity during this period',
            'Critical launch scheduled',
            'Multiple team members already on leave',
            'Business-critical meeting scheduled',
            'Year-end busy period',
            'Need team coverage for this period',
        ];

        return $reasons[array_rand($reasons)];
    }

    /**
     * Print summary of seeded data
     */
    protected function printSummary(Company $small, Company $medium, Company $large): void
    {
        $this->command->newLine();
        $this->command->info('📊 Test Data Summary:');
        $this->command->newLine();

        $this->command->table(
            ['Company', 'Users', 'Teams', 'Requests', 'Login Info'],
            [
                [
                    $small->name,
                    $small->users()->count(),
                    $small->teams()->count(),
                    VacationRequest::where('company_id', $small->id)->count(),
                    'owner@smallstartup.com / password',
                ],
                [
                    $medium->name,
                    $medium->users()->count(),
                    $medium->teams()->count(),
                    VacationRequest::where('company_id', $medium->id)->count(),
                    'owner@mediumbusiness.com / password',
                ],
                [
                    $large->name,
                    $large->users()->count(),
                    $large->teams()->count(),
                    VacationRequest::where('company_id', $large->id)->count(),
                    'ceo@largeenterprise.com / password',
                ],
            ]
        );

        $this->command->newLine();
        $this->command->info('💡 All test users use the password: password');
        $this->command->info('🔗 You can log in at: '.config('app.url').'/login');
        $this->command->newLine();
        $this->command->info('Test user examples:');
        $this->command->info('  • Small Company Owner: owner@smallstartup.com');
        $this->command->info('  • Medium Company Manager: manager0@mediumbusiness.com');
        $this->command->info('  • Large Company Employee: e0-1@largeenterprise.com');
        $this->command->newLine();
    }
}
