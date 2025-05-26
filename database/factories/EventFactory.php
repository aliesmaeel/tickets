<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => json_encode(['en' => 'Event 1', 'ar' => 'حدث 1', 'kur' => 'حدث 1']),
            'description' =>
                json_encode(['en' => 'Description for Event 1', 'ar' => 'وصف للحدث 1','kur' => 'وصف للحدث 1']),
            'type' => $this->faker->randomElement(['party', 'concert', 'football', 'conference']),
            'image' => $this->faker->imageUrl(640, 480, 'event', true, 'Event 1'),
            'address' => json_encode(['en' => 'Address for Event 1', 'ar' => 'عنوان للحدث 1', 'kur' => 'عنوان للحدث 1']),
            'address_link' => $this->faker->url,
            'start_time' => '2025-05-01 10:00:00',
            'end_time' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d H:i:s'),
            'display_start_date' => '2025-05-01',
            'display_end_date' => today()->addDays(2)->toDateString(),
            'category_id' => $this->faker->numberBetween(1, 5),
            'city_id' => $this->faker->numberBetween(1, 5),
            'max_cache_orders' => $this->faker->numberBetween(1, 100),
        ];
    }
}
