<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'ali',
            'phone' => '4917671948071',
            'password'=> bcrypt('123456789'),
            'lang'=> 'ar',
            'is_active'=> 1,
            'birth_date'=>'22-02-2022',
            'gender'=>'male',
        ];
    }
}
