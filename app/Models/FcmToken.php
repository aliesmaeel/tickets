<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FcmToken extends Model
{
    protected $table = 'fcm_tokens';

    protected $fillable = [
        'is_active',
        'userable_id',
        'userable_type',
        'fcm_token',
    ];

    public function userable()
    {
        return $this->morphTo();
    }
}
