<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected $casts = [
        'value' => 'float',
    ];

    public static function getRate(string $key, float $default = 1.0): float
    {
        return static::where('key', $key)->value('value') ?? $default;
    }
}
