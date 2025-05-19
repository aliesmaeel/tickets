<?php

namespace Database\Factories;

use App\Traits\MultiLanguageTrait;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\City>
 */
class CityFactory extends Factory
{
    public function definition(): array
    {

        $fakerEn = \Faker\Factory::create('en_US');
        $fakerAr = \Faker\Factory::create('ar_SA');
        $fakerKur = \Faker\Factory::create('fa_IR');

        return [
            'name' => [
                'en' => $fakerEn->city,
                'ar' => $fakerAr->city,
                'kur' => $fakerKur->city,
            ],
        ];
    }

}
