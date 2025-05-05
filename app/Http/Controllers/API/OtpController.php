<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Traits\ApiResponse;

class OtpController extends Controller
{
    use ApiResponse;

    public function send(Request $request, TwilioService $twilio)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $otp = rand(100000, 999999);

        Cache::put('otp_' . $request->phone, $otp, now()->addMinutes(5));

        $twilio->sendOtp($request->phone, "Your verification code is: $otp");

        return $this->success([], 'OTP sent successfully.');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|numeric',
        ]);

        $cachedOtp = Cache::get('otp_' . $request->phone);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return $this->error('Invalid or expired OTP', 401);
        }

        return $this->success([], 'OTP verified successfully.');
    }
}
