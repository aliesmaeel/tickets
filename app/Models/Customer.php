<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = ['name', 'phone', 'password', 'image', 'is_active','lang'];


    protected $hidden = ['password'];
}
