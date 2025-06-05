<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventSeat;
use App\Models\Role;
use App\Models\SeatClass;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Factories\CategoryFactory;
use Database\Factories\EventFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {



//
//        DB::table('model_has_roles')->insert([
//            'role_id' => 1,
//            'model_type' => 'App\Models\User',
//            'model_id' => 1,
//        ]);
//


//        Category::factory()->count(5)->create();
//        City::factory()->count(10)->create();
//        Event::factory()->count(30)->create();
//        Advertisement::factory()->count(5)->create();
//        Customer::factory()->count(1)->create();


        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
//            SeatClassSeeder::class,
//            EventSeatSeeder::class,
//            SettingsSeeder::class,
//            CouponSeeder::class,
        ]);

    }
}
