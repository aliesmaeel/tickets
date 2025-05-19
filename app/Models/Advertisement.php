<?php

namespace App\Models;

use App\Traits\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelPackageTools\Concerns\Package\HasTranslations;

class Advertisement extends Model
{

    use HasTranslations,HasFactory ,HasLocalizedAttributes;
    protected $fillable = [
        'title',
        'description',
        'link',
        'image',
        'active',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
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
