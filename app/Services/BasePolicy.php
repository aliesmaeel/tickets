<?php

namespace App\Services;

use App\Models\User;

class BasePolicy
{
    public string $suffix;

    public function __construct($suffix)
    {
        $this->suffix = $suffix;
    }

    public function before(User $user, $ability)
    {
    //        if (isSuperAdmin()) {
    //            return true;
    //        }
    }

    public function viewAny(\App\Models\User $user): bool
    {
        return $user->hasPermissionTo($this->preparePermissionName('view-any'));
    }

    public function view(\App\Models\User $user, mixed $model = null): bool
    {
        return $user->hasPermissionTo($this->preparePermissionName('view'));
    }

    public function create(\App\Models\User $user): bool
    {
        return $user->hasPermissionTo($this->preparePermissionName('create'));
    }

    public function update(\App\Models\User $user, mixed $model = null): bool
    {
        return $user->hasPermissionTo($this->preparePermissionName('update'));
    }

    public function delete(\App\Models\User $user, mixed $model = null): bool
    {
        return $user->hasPermissionTo($this->preparePermissionName('delete'));
    }

    public function restore(\App\Models\User $user, $model): bool
    {
        return $user->hasPermissionTo($this->preparePermissionName('restore'));
    }

    public function forceDelete(\App\Models\User $user, $model): bool
    {
        return $user->hasPermissionTo($this->preparePermissionName('force-delete'));
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
