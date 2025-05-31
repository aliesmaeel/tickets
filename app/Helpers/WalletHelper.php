<?php

namespace App\Helpers;

class WalletHelper
{
    public static function checkWalletHasEnoughMoney($wallet, $amount)
    {
        return $wallet->balance >= $amount;
    }
}
