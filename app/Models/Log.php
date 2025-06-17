<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{



    protected $fillable = [
        'id',
        'description',
        'user',
        'router',
        'method',
        'ip_address',
        'level',
        'level_name',
        'user_agent',
        'bug_info'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


}
