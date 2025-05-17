<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\Http;

class OtpService
{
    protected $apiToken;
    protected $sender = 'TickDot';
    protected $type = 'whatsapp';
    protected $lang = 'en';
    protected $sendUrl = 'https://gateway.standingtech.com/api/v4/sms/send';
    protected $verifyUrl = 'https://gateway.standingtech.com/api/v4/sms/verifyotp';

    public function __construct()
    {
        $this->apiToken = config('services.standingtech.token');
    }

    public function send($phone, $lang = null, $data = [])
    {
        $otp = rand(100000, 999999);
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($this->sendUrl, [
            'recipient' => $phone,
            'sender_id' => $this->sender,
            'type' => $this->type,
            'message' => (string) $otp,
            'lang' => $lang ?? $this->lang,
        ]);

        if ($response->successful() && $response->json('status') === 'success') {
            cache()->put("otp:{$phone}", [
                'code' => $otp,
                'id' => $response->json('id'),
            ], now()->addMinutes(10));

            if (!empty($data)) {
                cache()->put("register:{$phone}", $data, now()->addMinutes(10));
            }

            return true;
        }

        throw new \Exception($response->json('error.message') ?? 'Failed to send OTP');
    }



    public function verify($phone, $code)
    {

        $cached = cache("otp:{$phone}");

        if (!$cached || !isset($cached['id']) || !isset($cached['code'])) {
            return false;
        }

        if ($cached['code'] != $code) {
            return false;
        }

        cache()->forget("otp:{$phone}");
        return true;
    }
}

