<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'ali',
            'email' => 'ali@ali.com',
            'password' => bcrypt('123'),
        ]);

        User::factory()->create([
            'name' => 'mobileUser',
            'email' => 'mobile@mobile.com',
            'password' => bcrypt('123'),
        ]);


        DB::table('roles')->insert([
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);

        DB::table('model_has_roles')->insert([
            'role_id' => 1,
            'model_type' => 'App\Models\User',
            'model_id' => 1,
        ]);

        DB::table('roles')->insert([
            'name' => 'mobile-user',
            'guard_name' => 'api',
        ]);

        DB::table('model_has_roles')->insert([
            'role_id' => 2,
            'model_type' => 'App\Models\User',
            'model_id' => 2,
        ]);


    }
}
