<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $patronymic = fake()->firstName();

        return [
            'name' => $lastName . ' ' . $firstName . ' ' . $patronymic,
            'phone' => fake()->unique()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'user_id' => User::factory(),
        ];
    }
} 