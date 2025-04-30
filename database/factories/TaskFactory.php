<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        $statuses = ['new', 'in_progress', 'completed'];
        
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'deadline' => fake()->dateTimeBetween('now', '+1 month'),
            'status' => fake()->randomElement($statuses),
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
        ];
    }
} 