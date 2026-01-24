<?php

namespace Database\Seeders;

use App\Enum\SubscriptionInterval;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            // Free Plan
            [
                'name' => 'Free',
                'slug' => 'free',
                'price' => 0.00,
                'currency' => 'EUR',
                'interval' => SubscriptionInterval::MONTH,
                'max_users' => 6, // 5 employees + 1 owner
                'description' => 'Perfect for small startups and freelancers with tiny teams',
                'features' => [
                    'Up to 5 employees + owner',
                    'All core vacation management features',
                    'Basic calendar view',
                    'Email notifications',
                    'Mobile responsive',
                    'Community support only',
                ],
                'is_popular' => false,
                'sort_order' => 1,
            ],

            // Starter Monthly
            [
                'name' => 'Starter (Monthly)',
                'slug' => 'starter-monthly',
                'price' => 29.00,
                'currency' => 'EUR',
                'interval' => SubscriptionInterval::MONTH,
                'max_users' => 21, // 20 employees + 1 owner
                'description' => 'For small businesses ready to scale',
                'features' => [
                    'Up to 20 employees + owner',
                    'Everything in Free',
                    'Priority email support',
                    'Advanced analytics',
                    'Custom vacation policies',
                    'Department management',
                    'CSV export',
                ],
                'is_popular' => false,
                'sort_order' => 2,
            ],

            // Starter Yearly (20% discount)
            [
                'name' => 'Starter (Yearly)',
                'slug' => 'starter-yearly',
                'price' => 279.00,
                'currency' => 'EUR',
                'interval' => SubscriptionInterval::YEAR,
                'max_users' => 21, // 20 employees + 1 owner
                'description' => 'Save 20% with annual billing',
                'features' => [
                    'Up to 20 employees + owner',
                    'Everything in Free',
                    'Priority email support',
                    'Advanced analytics',
                    'Custom vacation policies',
                    'Department management',
                    'CSV export',
                    '💰 Save €69/year',
                ],
                'is_popular' => true,
                'sort_order' => 3,
            ],

            // Professional Monthly
            [
                'name' => 'Professional (Monthly)',
                'slug' => 'professional-monthly',
                'price' => 79.00,
                'currency' => 'EUR',
                'interval' => SubscriptionInterval::MONTH,
                'max_users' => 51, // 50 employees + 1 owner
                'description' => 'For growing companies with advanced needs',
                'features' => [
                    'Up to 50 employees + owner',
                    'Everything in Starter',
                    'Team management',
                    'Advanced reporting & insights',
                    'API access',
                    'Slack/Teams integration',
                    'Custom branding',
                    'Priority support (24h response)',
                ],
                'is_popular' => false,
                'sort_order' => 4,
            ],

            // Professional Yearly (20% discount)
            [
                'name' => 'Professional (Yearly)',
                'slug' => 'professional-yearly',
                'price' => 759.00,
                'currency' => 'EUR',
                'interval' => SubscriptionInterval::YEAR,
                'max_users' => 51, // 50 employees + 1 owner
                'description' => 'Save 20% with annual billing',
                'features' => [
                    'Up to 50 employees + owner',
                    'Everything in Starter',
                    'Team management',
                    'Advanced reporting & insights',
                    'API access',
                    'Slack/Teams integration',
                    'Custom branding',
                    'Priority support (24h response)',
                    '💰 Save €189/year',
                ],
                'is_popular' => true,
                'sort_order' => 5,
            ],

            // Enterprise Monthly
            [
                'name' => 'Enterprise (Monthly)',
                'slug' => 'enterprise-monthly',
                'price' => 199.00,
                'currency' => 'EUR',
                'interval' => SubscriptionInterval::MONTH,
                'max_users' => 201, // 200 employees + 1 owner
                'description' => 'For large companies with mission-critical needs',
                'features' => [
                    'Up to 200 employees + owner',
                    'Everything in Professional',
                    'Dedicated account manager',
                    'SLA guarantee (99.9% uptime)',
                    'Advanced security (SSO, 2FA)',
                    'Custom integrations',
                    'Onboarding assistance',
                    'Priority support (4h response)',
                    'White-label option',
                ],
                'is_popular' => false,
                'sort_order' => 6,
            ],

            // Enterprise Yearly (25% discount)
            [
                'name' => 'Enterprise (Yearly)',
                'slug' => 'enterprise-yearly',
                'price' => 1790.00,
                'currency' => 'EUR',
                'interval' => SubscriptionInterval::YEAR,
                'max_users' => 201, // 200 employees + 1 owner
                'description' => 'Save 25% with annual billing',
                'features' => [
                    'Up to 200 employees + owner',
                    'Everything in Professional',
                    'Dedicated account manager',
                    'SLA guarantee (99.9% uptime)',
                    'Advanced security (SSO, 2FA)',
                    'Custom integrations',
                    'Onboarding assistance',
                    'Priority support (4h response)',
                    'White-label option',
                    '💰 Save €598/year',
                ],
                'is_popular' => true,
                'sort_order' => 7,
            ],

            // Lifetime Plan
            [
                'name' => 'Lifetime',
                'slug' => 'lifetime',
                'price' => 2999.00,
                'currency' => 'EUR',
                'interval' => SubscriptionInterval::ONE_TIME,
                'max_users' => 201, // 200 employees + 1 owner
                'description' => '🌟 Pay once, use forever - Limited time offer!',
                'features' => [
                    'Up to 200 employees + owner',
                    'All Enterprise features',
                    'Lifetime updates',
                    'Lifetime support',
                    'Priority feature requests',
                    'Early access to new features',
                    '💎 One-time payment',
                    '🚀 Save €7,961 vs monthly (5 years)',
                    '⚡ Limited availability',
                ],
                'is_popular' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($plans as $plan) {
            Subscription::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('✓ Subscription plans seeded successfully!');
        $this->command->info('  - Free: 6 users (5 + owner)');
        $this->command->info('  - Starter: 21 users (20 + owner)');
        $this->command->info('  - Professional: 51 users (50 + owner)');
        $this->command->info('  - Enterprise: 201 users (200 + owner)');
        $this->command->info('  - Lifetime: 201 users (200 + owner)');
    }
}
