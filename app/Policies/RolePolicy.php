<?php

namespace App\Policies;

use App\Services\BasePolicy;

class RolePolicy extends BasePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        parent::__construct('Role');
    }

    public function viewAny(\App\Models\User $user): bool
    {
        return $user->isDeveloper();
    }
}
