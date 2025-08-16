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


        if($response->status() === 200) {
            return $response->json();
        }

        if ($response->status() === 404) {
            return [
              'result' => [
                'code' => $response->json()['result']['code'] ?? null,
                'description' => 'Transaction not found',
                'status_code' => 404
              ]
            ];
        }

         return null;

    }
}
