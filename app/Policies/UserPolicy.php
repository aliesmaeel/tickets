<?php

namespace App\Policies;

use App\Models\User;
use App\Services\BasePolicy;
use Illuminate\Auth\Access\Response;

class UserPolicy extends BasePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        parent::__construct('User');
    }
}
