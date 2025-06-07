<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use Illuminate\Http\Request;

class FcmController  extends Controller
{

    public function updateDeviceToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $fcm_token = $request->fcm_token;

        $fcmTokens = FcmToken::query()
            ->where('fcm_token', $fcm_token)
            ->where('is_active', 1)
            ->get();

        $fcmTokensCount = $fcmTokens->count();

        if ($fcmTokensCount == 1) {
            $token = $fcmTokens->first();

            if ($token->userable_id == auth()->user()->id && $token->userable_type == auth()->user()->getMorphClass()) {
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
