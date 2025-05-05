<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\SeatClass;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Factories\CategoryFactory;
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
            'name' => 'customer',
            'guard_name' => 'api',
        ]);

        DB::table('model_has_roles')->insert([
            'role_id' => 2,
            'model_type' => 'App\Models\User',
            'model_id' => 2,
        ]);

        Category::factory()->count(10)->create();
        City::factory()->count(10)->create();
        SeatClass::factory()->count(3)->create();

    }
}
