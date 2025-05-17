<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelPackageTools\Concerns\Package\HasTranslations;

class City extends Model
{
    use HasFactory,HasTranslations;

     protected $fillable = [
        'name',
    ];

    protected $casts = [
        'name' => 'array',
    ];
    public $translatable = [
        'name',
    ];
}
