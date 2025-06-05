<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{


    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'string',
            'guard_name' => 'string',
        ];
    }
}
{
}
