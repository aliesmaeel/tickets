<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SeatClass>
 */
class SeatClassFactory extends Factory
{
    protected $arr = ['empty','stage','reserved','Class A', 'Class B', 'Class C'];

    public function definition(): array
    {
        $name = array_pop($this->arr);

        $color = match ($name) {
            'empty' => '#000000',
            'reserved' => '#FFFF00',
            'stage' => '#808080',
            default => $this->faker->hexColor(),
        };

        return [
            'name' => $name,
            'price' => $this->faker->randomFloat(2, 10, 100),
            'color' => $color,
            'event_id' => 1,
        ];
    }
}
