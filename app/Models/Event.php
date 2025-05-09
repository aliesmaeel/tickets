<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelPackageTools\Concerns\Package\HasTranslations;

class Event extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'description',
        'image',
        'address',
        'address_link',
        'start_time',
        'end_time',
        'display_start_date',
        'display_end_date',
        'active',
        'type',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'display_start_date' => 'datetime',
        'display_end_date' => 'datetime',
        'description' => 'array',
        'name' => 'array',
    ];

    public $translatable = ['description','name'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
