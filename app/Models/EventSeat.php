<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSeat extends Model
{
    protected $table = 'event_seats';
    protected $fillable = [
        'event_id',
        'row',
        'col',
        'seat_class_id',
        'status',
    ];

    protected $casts = [
        'name' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function seatClass()
    {
        return $this->belongsTo(SeatClass::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_seat');
    }


}
