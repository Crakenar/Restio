<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Basic Plan', 'Pro Plan', 'Enterprise Plan']),
            'slug' => fake()->slug(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'interval' => fake()->randomElement([\App\Enum\SubscriptionInterval::MONTH, \App\Enum\SubscriptionInterval::YEAR, \App\Enum\SubscriptionInterval::ONE_TIME]),
        ];
    }
}
