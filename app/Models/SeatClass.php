<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatClass extends Model
{

    use HasFactory;

    protected $fillable = ['name', 'price', 'event_id','color'];

    public function eventSeats()
    {
        return $this->hasMany(EventSeat::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
