<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
use App\Models\EventSeat;
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

        Category::factory()->count(5)->create();
        City::factory()->count(10)->create();

        DB::table('events')->insert([
            'name' => json_encode(['en' => 'Event 1', 'ar' => 'حدث 1', 'kur' => 'حدث 1']),
            'description' =>
                json_encode(['en' => 'Description for Event 1', 'ar' => 'وصف للحدث 1','kur' => 'وصف للحدث 1']),
            'type' => 'party',
            'image' => 'https://example.com/image1.jpg',
            'address' => json_encode(['en' => 'Address for Event 1', 'ar' => 'عنوان للحدث 1', 'kur' => 'عنوان للحدث 1']),
            'address_link' => 'https://example.com/address1',
            'start_time' => '2025-05-01 10:00:00',
            'end_time' => '2025-05-01 12:00:00',
            'display_start_date' => '2025-05-01',
            'display_end_date' => '2025-05-01',
            'category_id' => 1,
            'city_id' => 1,
        ]);

        DB::table('events')->insert([
            'name' => json_encode(['en' => 'Event 2', 'ar' => 'حدث 2', 'kur' => 'حدث 2']),
            'description' => json_encode(['en' => 'Description for Event 2', 'ar' => 'وصف للحدث 2', 'kur' => 'وصف للحدث 2']),
            'type' => 'football',
            'image' => 'https://example.com/image2.jpg',
            'address' => json_encode(['en' => 'Address for Event 2', 'ar' => 'عنوان للحدث 2', 'kur' => 'عنوان للحدث 2']),
            'address_link' => 'https://example.com/address2',
            'start_time' => '2025-05-02 14:00:00',
            'end_time' => '2025-05-02 16:00:00',
            'display_start_date' => '2025-05-02',
            'display_end_date' => '2025-05-02',
            'category_id' => 2,
            'city_id' => 2,
        ]);

        Advertisement::factory()->count(5)->create();
        Customer::factory()->count(1)->create();
        $this->call([
            SeatClassSeeder::class,
            EventSeatSeeder::class,
            SettingsSeeder::class,
        ]);

    }
}
