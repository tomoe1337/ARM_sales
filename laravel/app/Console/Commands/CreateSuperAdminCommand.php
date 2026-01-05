<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class CreateSuperAdminCommand extends Command
{
    protected $signature = 'user:create-super-admin 
                            {--email= : Email супер-админа}
                            {--password= : Пароль}
                            {--name= : Имя}';

    protected $description = 'Создать супер-администратора системы';

    public function handle(): int
    {
        $email = $this->option('email') ?? $this->ask('Email');
        $password = $this->option('password') ?? $this->secret('Пароль');
        $name = $this->option('name') ?? $this->ask('Имя', 'Super Admin');

        // Валидация
        $validator = Validator::make([
            'email' => $email,
            'password' => $password,
            'name' => $name,
        ], [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return Command::FAILURE;
        }

        // Проверяем существование роли
        $role = Role::firstOrCreate(
            ['name' => 'super_admin'],
            ['guard_name' => 'web']
        );

        // Создаем пользователя
        $user = User::create([
            'name' => $name,
            'full_name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'organization_id' => null, // Супер-админ не привязан к организации
            'department_id' => null,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        // Назначаем роль
        $user->assignRole($role);

        $this->info("✓ Супер-администратор создан:");
        $this->line("  Email: {$email}");
        $this->line("  Имя: {$name}");
        $this->line("  ID: {$user->id}");

        return Command::SUCCESS;
    }
}

