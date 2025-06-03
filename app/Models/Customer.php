<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use HasApiTokens,HasFactory,Notifiable;

    protected $fillable = ['name', 'phone', 'password', 'image', 'is_active','lang','birth_date','gender'];

    protected $hidden = ['password', 'remember_token', 'created_at', 'updated_at'];

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    protected static function booted()
    {
        static::created(function ($customer) {
            $customer->wallet()->create();
        });
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }
}
