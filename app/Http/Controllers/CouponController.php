<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    use ApiResponse;
    public function apply(Request $request)
    {
        $validated = $request->validate([
            'total_price' => 'required|numeric',
            'coupon_code' => 'required|string',
        ]);

        $coupon = Coupon::where('code', $validated['coupon_code'])->first();

        if (! $coupon || ! $coupon->isValid()) {
            return $this->respondError('Invalid or expired coupon code.');
        }

        $newTotal = $coupon->applyDiscount($validated['total_price']);

        return $this->respondValue(
            [
                'new_total' => $newTotal,
                'discount' => $validated['total_price'] - $newTotal,
            ],
            'Coupon applied successfully.'
        );
    }

}
