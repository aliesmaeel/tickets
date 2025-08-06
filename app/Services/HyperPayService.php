<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class HyperPayService
{
    public function verifyPaymentByTransactionId(string $merchantTransactionId): ?array
    {
        $entityId = config('services.hyperpay.entity_id');
        $token = config('services.hyperpay.token');

        $url = config('services.hyperpay.test_url_verify') . '?merchantTransactionId=' . $merchantTransactionId . '&entityId=' . $entityId;

        $response = Http::withHeaders([
            'Authorization' => "Bearer $token",
        ])->get($url);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('HyperPay verification failed', ['transaction_id' => $merchantTransactionId, 'response' => $response->body()]);
        return null;
    }
}
