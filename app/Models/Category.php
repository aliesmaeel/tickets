<?php

namespace App\Models;

use App\Traits\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelPackageTools\Concerns\Package\HasTranslations;

class Category extends Model
{
    use HasTranslations,HasFactory,HasLocalizedAttributes;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'status',
    ];

    protected $casts = [
        'name' => 'array',
    ];
    public $translatable = [
        'name',
    ];

}
