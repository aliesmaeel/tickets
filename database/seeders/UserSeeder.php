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


        $superAdminDevelperMajd =  User::updateOrCreate(
            [
                'email' => 'majd@gmail.com',
            ],
            [  'name' => 'Majd',
                'password' => bcrypt('password')
            ]);
        $superAdminDevelperMajd->assignRole('super-admin');


        $superAdminDevelperMajd =  User::updateOrCreate(
            [
                'email' => 'ali@gmail.com',
            ],
            [  'name' => 'Ali',
                'password' => bcrypt('password')
            ]);
        $superAdminDevelperMajd->assignRole('super-admin');

    }
}
