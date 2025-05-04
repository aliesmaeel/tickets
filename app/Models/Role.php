<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends \Spatie\Permission\Models\Role
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
