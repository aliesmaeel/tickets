<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    use ApiResponse;
    public function convertPointsToMoney(Request $request)
    {
        $request->validate([
            'points' => 'required|integer|min:1',
        ]);

        $customer = auth()->user();
        $wallet = $customer->wallet;

        if ($wallet->points < $request->points) {
            return $this->respondError(
                __('messages.insufficient_points'),
                null,
                422
            );
        }

        $rate = Setting::getRate('point_to_money_rate');
        $money = $request->points * $rate;
        $money = number_format($money, 2, '.', '');

        DB::beginTransaction();
        try {
            $wallet->decrement('points', $request->points);
            $wallet->increment('balance', $money);

            DB::commit();

            return $this->respondValue(
                [
                    'converted_points' => $request->points,
                    'original_balance' => $wallet->balance - $money,
                    'new_balance' => $wallet->balance,
                    'points_after' => $wallet->points,
                ],
                __('messages.points_converted_successfully')
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->respondError(
                __('messages.something_went_wrong'),
                null,
                500
            );
        }
        }
}
