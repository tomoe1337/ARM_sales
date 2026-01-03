<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем роли, если их еще нет
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'organization_owner']);
        Role::firstOrCreate(['name' => 'head']);
        Role::firstOrCreate(['name' => 'manager']);
        
        $this->command->info('Роли созданы: super_admin, organization_owner, head, manager');
    }
}

