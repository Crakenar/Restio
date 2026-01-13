<?php

namespace Database\Seeders;

use App\Enum\SubscriptionInterval;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        Subscription::create([
            'name' => 'Monthly',
            'slug' => 'monthly',
            'price' => 29.00,
            'currency' => 'EUR',
            'interval' => SubscriptionInterval::MONTH,
        ]);

        Subscription::create([
            'name' => 'Yearly',
            'slug' => 'yearly',
            'price' => 290.00,
            'currency' => 'EUR',
            'interval' => SubscriptionInterval::YEAR,
        ]);

        Subscription::create([
            'name' => 'Lifetime',
            'slug' => 'lifetime',
            'price' => 999.00,
            'currency' => 'EUR',
            'interval' => SubscriptionInterval::ONE_TIME,
        ]);
    }
}
