<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Task;
use App\Models\WorkSession;
use App\Models\Plan;
use Illuminate\Support\Collection;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       /* $this->call([
            DealSeeder::class,
        ]);
*/
        $faker = Faker::create('ru_RU');

        // Создаем менеджера, если его ещё нет
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'full_name' => 'Иванов Иван Иванович',
                'login' => 'manager',
                'password' => Hash::make('password'),
                'role' => 'head'
            ]
        );

        // Создаем пользователей
        $users = collect([$manager]); // добавляем менеджера в коллекцию

        for ($i = 1; $i <= 5; $i++) {
            $user = User::firstOrCreate(
                ['email' => 'employee' . $i . '@example.com'],
                [
                    'full_name' => 'Сотрудник ' . $i,
                    'login' => 'employee' . $i,
                    'password' => Hash::make('password'),
                    'role' => 'manager'
                ]
            );
            $users->push($user);
        }

        // Для каждого пользователя создаем клиентов
        $users->each(function ($user) use ($faker) {
            for ($i = 1; $i <= 3; $i++) {
                $client = Client::create([
                    'name' => 'Клиент ' . $i . ' пользователя ' . $user->id,
                    'phone' => '+7' . $faker->numerify('##########'),
                    'email' => 'client' . $i . '_user' . $user->id . '@example.com',
                    'address' => $faker->address(),
                    'description' => $faker->paragraph(),
                    'user_id' => $user->id
                ]);

                // Для каждого клиента создаем сделки (все в мае 2025)
                for ($j = 1; $j <= 2; $j++) {
                    Deal::create([
                        'title' => 'Сделка ' . $j . ' клиента ' . $i,
                        'description' => $faker->paragraph(),
                        'amount' => $faker->randomFloat(2, 1000, 100000),
                        'status' => $faker->randomElement(['new', 'in_progress', 'won', 'lost']),
                        'closed_at' => $faker->dateTimeBetween('2025-05-01', '2025-05-31'),
                        'user_id' => $user->id,
                        'client_id' => $client->id
                    ]);
                }

                // Для каждого клиента создаем задачи
                for ($k = 1; $k <= 2; $k++) {
                    Task::create([
                        'title' => 'Задача ' . $k,
                        'description' => $faker->paragraph(),
                        'deadline' => $faker->dateTimeBetween('now', '+1 month'),
                        'status' => $faker->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
                        'user_id' => $user->id,
                        'assignee_id' => $user->id
                    ]);
                }

                // С вероятностью 50% создаём рабочую сессию
                if (rand(0, 1)) {
                    WorkSession::create([
                        'user_id' => $user->id,
                        'start_time' => now()->subHours(rand(1, 8)),
                        'end_time' => null
                    ]);
                }
            }
        });

        // Создаем планы только один раз для первых 3 пользователей
        for ($i = 1; $i <= 3; $i++) {
            Plan::firstOrCreate(
                ['user_id' => $i],
                [
                    'monthly_plan' => $faker->numberBetween(50000, 200000),
                    'daily_plan' => $faker->numberBetween(1000, 10000),
                ]
            );
        }
    }
}
