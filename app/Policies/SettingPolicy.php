<?php

namespace App\Policies;

use App\Models\User;
use App\Services\BasePolicy;

class SettingPolicy extends BasePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        parent::__construct('Setting');
    }
}

