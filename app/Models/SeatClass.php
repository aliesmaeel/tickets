<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatClass extends Model
{

    use HasFactory;

    protected $fillable = ['name', 'price', 'event_id','color'];


    protected $hidden = ['created_at', 'updated_at'];
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
