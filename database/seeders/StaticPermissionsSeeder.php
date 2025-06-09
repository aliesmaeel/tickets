<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Support\StaticPermissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class StaticPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Collection::wrap(StaticPermissions::allAdminPermissions())
            ->each(function ($permission) {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            });
    }
}
