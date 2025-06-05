<?php

namespace App\Policies;

use App\Services\BasePolicy;

class CategoryPolicy extends BasePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        parent::__construct('Category');
    }
}
