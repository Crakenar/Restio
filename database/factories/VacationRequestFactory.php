<?php

namespace Database\Factories;

use App\Enum\VacationRequestStatus;
use App\Enum\VacationRequestType;
use App\Models\Company;
use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VacationRequest>
 */
class VacationRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'start_date' => now(),
            'end_date' => now()->addDay(),
        ];
    }

    /**
     * Indicate that the request is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VacationRequestStatus::PENDING,
            // Pending requests are typically for future dates
            'start_date' => fake()->dateTimeBetween('+1 week', '+2 months'),
        ])->afterMaking(function (VacationRequest $request) {
            $request->end_date = (clone $request->start_date)->modify('+'.fake()->numberBetween(1, 7).' days');
        });
    }

    /**
     * Indicate that the request is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VacationRequestStatus::APPROVED,
        ]);
    }

    /**
     * Indicate that the request is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VacationRequestStatus::REJECTED,
            'rejection_reason' => fake()->sentence(),
        ]);
    }

    /**
     * Create a vacation type request.
     */
    public function vacation(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => VacationRequestType::VACATION,
        ]);
    }

    /**
     * Create a sick leave request.
     */
    public function sick(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => VacationRequestType::SICK,
            // Sick leave is usually in the past or very recent
            'start_date' => fake()->dateTimeBetween('-1 month', 'now'),
        ])->afterMaking(function (VacationRequest $request) {
            $request->end_date = (clone $request->start_date)->modify('+'.fake()->numberBetween(1, 5).' days');
        });
    }

    /**
     * Create a work from home request.
     */
    public function wfh(): static
    {
        return $this->state(function (array $attributes) {
            $date = fake()->dateTimeBetween('-1 month', '+1 month');

            return [
                'type' => VacationRequestType::WORK_FROM_HOME,
                'start_date' => $date,
                'end_date' => $date, // WFH is usually single day
            ];
        });
    }

    /**
     * Create a request for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'company_id' => $user->company_id,
        ]);
    }
}
