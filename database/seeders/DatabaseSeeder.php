<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Task;
use App\Models\WorkSession;
use Illuminate\Support\Facades\Hash;
use App\Models\Plan;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Создаем менеджера
        $manager = User::create([
            'full_name' => 'Иванов Иван Иванович',
            'login' => 'manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role' => 'head'
        ]);

        // Создаем обычных пользователей
        $users = collect();
        for ($i = 1; $i <= 5; $i++) {
            $users->push(User::create([
                'full_name' => 'Сотрудник ' . $i,
                'login' => 'employee' . $i,
                'email' => 'employee' . $i . '@example.com',
                'password' => Hash::make('password'),
                'role' => 'manager'
            ]));
        }

        // Для каждого пользователя создаем клиентов
        $users->each(function ($user) {
            for ($i = 1; $i <= 3; $i++) {
                $client = Client::create([
                    'name' => 'Клиент ' . $i . ' пользователя ' . $user->id,
                    'phone' => '+7' . fake()->numerify('##########'),
                    'email' => 'client' . $i . '_user' . $user->id . '@example.com',
                    'address' => fake()->address(),
                    'description' => fake()->paragraph(),
                    'user_id' => $user->id
                ]);

                // Для каждого клиента создаем сделки
                for ($j = 1; $j <= 2; $j++) {
                    $deal = Deal::create([
                        'title' => 'Сделка ' . $j . ' клиента ' . $i,
                        'description' => fake()->paragraph(),
                        'amount' => fake()->randomFloat(2, 1000, 100000),
                        'status' => fake()->randomElement(['open', 'won', 'lost']),
                        'closed_at' => fake()->dateTimeBetween('now', '+1 year'),
                        'user_id' => $user->id,
                        'client_id' => $client->id
                    ]);

                    // Для каждой сделки создаем задачи
                    for ($k = 1; $k <= 2; $k++) {
                        Task::create([
                            'title' => 'Задача ' . $k . ' сделки ' . $j,
                            'description' => fake()->paragraph(),
                            'deadline' => fake()->dateTimeBetween('now', '+1 month'),
                            'status' => fake()->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
                            'user_id' => $user->id,
                            'assignee_id' => $user->id
                        ]);
                    }
                }
            }

            // Создаем рабочие сессии для некоторых пользователей
            if (rand(0, 1)) {
                WorkSession::create([
                    'user_id' => $user->id,
                    'start_time' => now()->subHours(rand(1, 8)),
                    'end_time' => null
                ]);
            }
        });

        $user = User::create([
            'full_name' => 'Test User',
            'login' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'head',
        ]);

        Plan::create([
            'user_id' => $user->id,
            'monthly_plan' => 1000,
            'daily_plan' => 50,
        ]);
    }
}
