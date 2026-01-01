<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DealFactory extends Factory
{
    public function definition(): array
    {
        $statuses = ['new', 'in_progress', 'won', 'lost'];

        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'amount' => fake()->randomFloat(2, 1000, 100000),
            'status' => fake()->randomElement(['open', 'won', 'lost']),
            'closed_at' => fake()->dateTimeBetween('now', '+1 year'),
            'user_id' => User::factory(),
            'client_id' => Client::factory(),

        ];
    }
}
