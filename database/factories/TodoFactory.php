<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Todo>
 */
class TodoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'assignee' => fake()->name(),
            'due_date' => fake()->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'time_tracked' => fake()->numberBetween(0, 5000),
            'status' => fake()->randomElement(['pending', 'open', 'in_progress', 'completed']),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
        ];
    }
}
