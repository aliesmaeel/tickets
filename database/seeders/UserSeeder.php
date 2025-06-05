<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $superAdmin =  User::updateOrCreate(
           [
              'email' => 'ali@ali.com',
           ],
           [  'name' => 'ali',
               'password' => bcrypt('123')
           ]);

        $superAdmin->assignRole('super-admin');
    }
}
