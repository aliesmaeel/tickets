<?php

namespace App\Models;

use App\Traits\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelPackageTools\Concerns\Package\HasTranslations;

class Event extends Model
{
    use HasTranslations,HasLocalizedAttributes,HasFactory;



    protected $fillable = [
        'name',
        'description',
        'image',
        'address',
        'address_link',
        'address_image',
        'start_time',
        'end_time',
        'display_start_date',
        'display_end_date',
        'active',
        'type',
        'max_cache_orders',
        'time_to_place_cache_order',
        'is_notified'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'display_start_date' => 'datetime',
        'display_end_date' => 'datetime',
        'description' => 'array',
        'name' => 'array',
        'address' => 'array',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
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

    // Event.php

    public function seatClasses()
    {
        return $this->hasMany(SeatClass::class);
    }

    public function seats()
    {
        return $this->hasMany(EventSeat::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
