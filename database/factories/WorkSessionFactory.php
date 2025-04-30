<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'start_time' => fake()->dateTimeBetween('-8 hours', 'now'),
            'end_time' => fake()->optional(0.3)->dateTimeBetween('now', '+8 hours'),
        ];
    }
} 