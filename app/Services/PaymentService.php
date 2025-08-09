<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaymentService
{
    public static function charge($amount,$merchantTransactionId) :array
    {
        $token = config('services.hyperpay.token');
        $entityId = config('services.hyperpay.entity_id');
        $currency = config('services.hyperpay.currency');
        $url = config('services.hyperpay.test_url_checkout');
        $shopperResultUrl = 'com.iraqiculture.tickdot://payment_process';

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer $token",
            ])->asForm()->post($url, [
                'entityId'    => $entityId,
                'amount'      => number_format($amount, 2, '.', ''),
                'currency'    => $currency,
                'paymentType' => 'DB',
                'merchantTransactionId' => $merchantTransactionId,
            ]);


            if ($response->json()['result']['code'] ==='000.200.100') {
                $data = $response->json();
                if (isset($data['id'])) {
                    return [
                        'success' => true,
                        'id' => $data['id'],
                    ];
                }
            }

            logger()->error('HyperPay payment failed', ['response' => $response->body()]);
        } catch (\Exception $e) {
            logger()->error('Exception during HyperPay charge', ['error' => $e->getMessage()]);
        }
        return [
            'success' => false,
            'message' => 'Payment failed, please try again later.',
        ];
    }
}
