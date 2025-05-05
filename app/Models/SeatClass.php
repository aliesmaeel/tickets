<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatClass extends Model
{

    use HasFactory;

    protected $fillable = ['name', 'price'];

    public function eventSeats()
    {
        return $this->hasMany(EventSeat::class);
    }
}
