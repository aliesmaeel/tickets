<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Advertisement>
 */
class AdvertisementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => [
                'en' => $this->faker->sentence,
                'ar' => $this->faker->sentence,
                'kur'=> $this->faker->sentence,
            ],
            'description' => [
                'en' => $this->faker->paragraph,
                'ar' => $this->faker->paragraph,
                'kur'=> $this->faker->paragraph,
            ],
            'image' => $this->faker->imageUrl(),
            'active' => $this->faker->randomElement([true, false]),
            'link' => $this->faker->url,
        ];
    }
}
