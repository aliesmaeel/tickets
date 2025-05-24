<?php

namespace App\Models;

use App\Traits\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasLocalizedAttributes;

    protected $fillable = ['order_id', 'order_seat_id', 'status','customer_id','event_id'];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderSeat()
    {
        return $this->belongsTo(OrderSeat::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }


}
