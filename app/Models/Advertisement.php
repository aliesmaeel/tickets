<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelPackageTools\Concerns\Package\HasTranslations;

class Advertisement extends Model
{

    use HasTranslations,HasFactory;
    protected $fillable = [
        'title',
        'description',
        'link',
        'image',
        'active',
    ];

    public $translatable = [
        'title',
        'description',
    ];
    protected $casts = [
        'title' => 'array',
        'description' => 'array',
    ];
}
