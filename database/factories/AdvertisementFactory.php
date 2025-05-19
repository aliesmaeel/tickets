<?php

namespace Database\Factories;

use App\Traits\MultiLanguageTrait;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Advertisement>
 */
class AdvertisementFactory extends Factory
{

    public function definition(): array
    {
        $fakerEn = \Faker\Factory::create('en_US');
        $fakerAr = \Faker\Factory::create('ar_SA');
        $fakerKur = \Faker\Factory::create('fa_IR');

        return [
            'title' => [
                'en' => $fakerEn->city,
                'ar' => $fakerAr->city,
                'kur' => $fakerKur->city,
            ],
            'description' => [
                'en' => $fakerEn->text,
                'ar' => $fakerAr->city.$fakerAr->city.$fakerAr->city.$fakerAr->city,
                'kur' => $fakerKur->city.$fakerKur->city.$fakerKur->city.$fakerKur->city,
            ],
            'image' => $fakerEn->imageUrl(640, 480, 'cats'),
            'link' => $fakerEn->url,
            'active' => $this->faker->randomElement([true, false]),
        ];
    }
}
