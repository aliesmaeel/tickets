<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SeatClass>
 */
class SeatClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $arr = ['Class A', 'Class B', 'Class C'];
    public function definition(): array
    {
        return [
            'name' => array_pop($this->arr),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'color' => $this->faker->hexColor(),
            'event_id' => 1,
        ];
    }
}
