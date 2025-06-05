<?php

namespace App\Policies;

use App\Services\BasePolicy;

class PermissionPolicy extends BasePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        parent::__construct('Permission');
    }

    public function viewAny(\App\Models\User $user): bool
    {
        return $user->isDeveloper();
    }

    public function view(\App\Models\User $user, mixed $model = null): bool
    {
        return $user->isDeveloper();
    }

    public function create(\App\Models\User $user): bool
    {
        return $user->isDeveloper();
    }

    public function update(\App\Models\User $user, mixed $model = null): bool
    {
        return $user->isDeveloper();
    }

    public function delete(\App\Models\User $user, mixed $model = null): bool
    {
        return $user->isDeveloper();
    }

    public function restore(\App\Models\User $user, $model): bool
    {
        return $user->isDeveloper();
    }

    public function forceDelete(\App\Models\User $user, $model): bool
    {
        return $user->isDeveloper();
    }

    public function print(\App\Models\User $user): bool
    {
        return $user->hasPermissionTo($this->preparePermissionName('print'));
    }

    public function import(\App\Models\User $user): bool
    {
        return $user->hasPermissionTo($this->preparePermissionName('import'));
    }

    public function export(\App\Models\User $user): bool
    {
        return $user->hasPermissionTo($this->preparePermissionName('export'));
    }

    private function preparePermissionName($functionName): string
    {
        // return PermissionController::permissionNameFormat($functionName.' '.$this->suffix);
        return $functionName.' '.$this->suffix;
    }
}
