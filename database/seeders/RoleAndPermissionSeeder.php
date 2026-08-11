<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['super_admin', 'company_admin', 'operator'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'sanctum']);
        }

        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@riopark.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'active' => true,
            ]
        );

        $superAdmin->syncRoles(['super_admin']);
    }
}
