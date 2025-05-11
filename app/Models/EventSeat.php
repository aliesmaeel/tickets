<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSeat extends Model
{

    protected $fillable = [
        'event_id',
        'seat_class_id',
        'seat_number',
        'is_reserved',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

}
