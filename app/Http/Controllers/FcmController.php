<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FcmController  extends Controller
{
    use ApiResponse;
    public function updateDeviceToken(Request $request)
    {


        $data = $request->only('fcm_token');

        $validator = Validator::make($data, [
            'fcm_token' =>  'required|string'
        ]);

        if ($validator->fails()) {
            return $this->respondValidationErrors($validator->errors()->toArray());
        }

        $fcm_token = $data['fcm_token'];

        $fcmTokens = FcmToken::query()
            ->where('fcm_token', $fcm_token)
            ->where('is_active', 1)
            ->get();

        $fcmTokensCount = $fcmTokens->count();

        if ($fcmTokensCount == 1) {
            $token = $fcmTokens->first();

            if ($token->userable_id == auth()->user()?->id && $token->userable_type == auth()->user()->getMorphClass()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device token already exists',
                    'data' => null,
                ]);
            }

            $token->update([
                'is_active' => 0,
            ]);
        } elseif ($fcmTokensCount > 1) {
            $fcmTokens->each(function ($token): void {
                $token->update([
                    'is_active' => 0,
                ]);
            });
        }

        auth()->user()->fcmTokens()->create([
            'fcm_token' => $fcm_token,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device token updated successfully',
            'data' => null,
        ]);
    }
}
