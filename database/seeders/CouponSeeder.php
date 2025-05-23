<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Coupon::create([
            'title'=>'coupon 1',
            'code' => 'FLC318',
            'type' => 'percentage',
            'value' => 30,
            'expires_at' => now()->addDays(30),
            'max_uses' => 100,
            'used_count' => 0,
            'is_active' => true,
        ]);
    }
}
