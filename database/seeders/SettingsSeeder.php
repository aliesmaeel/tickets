<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Setting::updateOrCreate(
            ['key' => 'money_to_point_rate'],
            ['value' => 0.055]
        );

        Setting::updateOrCreate(
            ['key' => 'point_to_money_rate'],
            ['value' => 0.07692]
        );
    }

}
