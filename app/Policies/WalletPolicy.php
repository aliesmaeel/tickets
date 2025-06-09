<?php

namespace App\Policies;

use App\Models\User;
use App\Services\BasePolicy;

class WalletPolicy extends BasePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        parent::__construct('Wallet');
    }
}
