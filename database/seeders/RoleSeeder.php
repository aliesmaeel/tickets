<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = Role::updateOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);

        $allPermissions = Permission::all();

        $superAdminRole->syncPermissions($allPermissions);
    }
}
