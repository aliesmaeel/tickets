<?php

namespace Database\Seeders;

use App\Models\SeatClass;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeatClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seatClasses = [
            1 => ['empty', 'stage', 'Class A', 'Class B', 'Class C'],
            2 => ['empty', 'stage', 'Gold', 'Silver', 'Bronze'],
        ];

        $defaultColors = [
            'empty' => '#000000',
            'stage' => '#808080',
        ];

        foreach ($seatClasses as $eventId => $names) {
            foreach ($names as $name) {
                SeatClass::create([
                    'event_id' => $eventId,
                    'name' => $name,
                    'color' => $defaultColors[$name] ?? fake()->hexColor(),
                    'price' => in_array($name,['empty','stage']) ? 0 : fake()->randomNumber(2, 10, 100),
                ]);
            }
        }
    }
}
