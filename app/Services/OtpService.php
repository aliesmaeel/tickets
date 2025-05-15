<?php

namespace App\Services;

class OtpService
{
    public function send(string $phone): bool
    {
        // Simulate sending OTP
        $otp = rand(100000, 999999);

        // Save OTP temporarily
        cache()->put("otp:$phone", $otp, now()->addMinutes(5));

        // Log for testing
        logger("OTP for $phone: $otp");

        return true;
    }

    public function verify(string $phone, string $otp): bool
    {
        return cache("otp:$phone") == $otp;
    }
}

