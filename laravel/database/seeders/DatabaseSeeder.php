<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Organization;
use App\Models\Department;
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
        // Создаем тарифный план
        $this->call([
            SubscriptionPlanSeeder::class,
        ]);
        
        // Отключен ClientOrderSeeder
        // $this->call([
        //     ClientOrderSeeder::class,
        // ]);
        
        $faker = Faker::create('ru_RU');

        // Создаем тестовую организацию
        $organization = Organization::firstOrCreate(
            ['name' => 'Тестовая организация'],
            [
                'email' => 'test@example.com',
                'phone' => '+7 (999) 123-45-67',
                'is_active' => true,
                'is_single_department' => true,
            ]
        );

        // Создаем тестовый департамент
        $department = Department::firstOrCreate(
            ['name' => 'Отдел продаж', 'organization_id' => $organization->id],
            [
                'description' => 'Тестовый отдел продаж',
                'is_active' => true,
            ]
        );

        // Создаем пользователей без проверки лимита (обходим Observer)
        User::withoutEvents(function () use ($organization, $department, &$manager, &$users) {
            // Создаем руководителя отдела
            $manager = User::firstOrCreate(
                ['email' => 'manager@example.com'],
                [
                    'name' => 'Иван',
                    'full_name' => 'Иванов',
                    'password' => Hash::make('password'),
                    'role' => 'head',
                    'organization_id' => $organization->id,
                    'department_id' => $department->id,
                    'is_active' => true,
                ]
            );

#todo: Оставить только для тест окружения
            // Создаем админа (без организации и департамента)
            User::firstOrCreate(
                ['email' => 'admin@mail.com'],
                [
                    'name' => 'Иван',
                    'full_name' => 'Иванов',
                    'password' => Hash::make('admin'),
                    'role' => 'admin',
                    'is_active' => true,
                ]
            );

            // Создаем пользователей (менеджеров) в организации и департаменте
            $users = collect([$manager]); // добавляем менеджера в коллекцию

            for ($i = 1; $i <= 5; $i++) {
                $user = User::firstOrCreate(
                    ['email' => 'employee' . $i . '@example.com'],
                    [
                        'name' => 'Сотрудник ',
                        'full_name' => $i,
                        'password' => Hash::make('password'),
                        'role' => 'manager',
                        'organization_id' => $organization->id,
                        'department_id' => $department->id,
                        'is_active' => true,
                    ]
                );
                $users->push($user);
            }
        });

        // Обновляем департамент, указывая руководителя
        $department->update(['head_id' => $manager->id]);

        // Для каждого пользователя создаем задачи
        $users->each(function ($user) use ($faker, $organization, $department) {
            for ($k = 1; $k <= 5; $k++) {
                Task::create([
                    'title' => 'Задача ' . $k,
                    'description' => $faker->paragraph(),
                    'deadline' => $faker->dateTimeBetween('now', '+1 month'),
                    'status' => $faker->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
                    'user_id' => $user->id,
                    'assignee_id' => $user->id,
                    'organization_id' => $user->organization_id,
                    'department_id' => $user->department_id,
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
