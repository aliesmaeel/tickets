<?php

namespace App\Services;

class PaymentService
{
    public static function charge($amount)
    {

        return $amount <= 20;
    }
}
