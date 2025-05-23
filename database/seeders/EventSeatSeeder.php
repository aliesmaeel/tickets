<?php

namespace Database\Seeders;

use App\Models\EventSeat;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    private $rows=[0,1,2];
    private $cols=[0,1,2];

    private $events =[1,2];

    private $eventSeatClasses = [10,3];

    public function run(): void
    {
        foreach ($this->events as $event){
            $seatClass = array_pop($this->eventSeatClasses);
            for ($row = 0; $row < count($this->rows); $row++) {
                for ($col = 0; $col < count($this->cols); $col++) {

                        EventSeat::create([
                            'event_id' => $event,
                            'row' => $row,
                            'col' => $col,
                            'seat_class_id' => $seatClass,
                            'status' => 'Available',
                        ]);
                }
            }
        }

    }
}
