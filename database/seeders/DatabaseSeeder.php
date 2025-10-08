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
        $this->call([
            ClientOrderSeeder::class,
        ]);
        
        $faker = Faker::create('ru_RU');

        // Создаем менеджера, если его ещё нет
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Иван',
                'full_name' => 'Иванов',
                'login' => 'manager',
                'password' => Hash::make('password'),
                'role' => 'head'
            ]
        );
#todo: Оставить только для тест окружения
        User::firstOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'Иван',
                'full_name' => 'Иванов',
                'login' => 'admin',
                'password' => Hash::make('admin'),
                'role' => 'admin'
            ]
        );

        // Создаем пользователей
        $users = collect([$manager]); // добавляем менеджера в коллекцию

        for ($i = 1; $i <= 5; $i++) {
            $user = User::firstOrCreate(
                ['email' => 'employee' . $i . '@example.com'],
                [
                    'name' => 'Сотрудник ',
                    'full_name' => $i,
                    'login' => 'employee' . $i,
                    'password' => Hash::make('password'),
                    'role' => 'manager'
                ]
            );
            $users->push($user);
        }

        // Для каждого пользователя создаем задачи
        $users->each(function ($user) use ($faker) {
            for ($k = 1; $k <= 5; $k++) {
                Task::create([
                    'title' => 'Задача ' . $k,
                    'description' => $faker->paragraph(),
                    'deadline' => $faker->dateTimeBetween('now', '+1 month'),
                    'status' => $faker->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
                    'user_id' => $user->id,
                    'assignee_id' => $user->id
                ]);
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
