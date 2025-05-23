<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderSeat extends Model
{
    protected $table = 'order_seat';

    protected $fillable = ['order_id', 'event_seat_id'];

    public $timestamps = false;

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function eventSeat()
    {
        return $this->belongsTo(EventSeat::class, 'event_seat_id');
    }
}
