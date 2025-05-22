<?php

namespace App\Models;

use App\Traits\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory,HasLocalizedAttributes;

    protected $fillable = [
        'customer_id',
        'event_id',
        'status',
        'total_price',
        'point_conversion_rate',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function seats()
    {
        return $this->belongsToMany(EventSeat::class, 'order_seat');
    }

}

