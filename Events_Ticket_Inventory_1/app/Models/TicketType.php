<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    protected $fillable = [
       'event_id',
        'name',
        'price',
        'quantity'
    ];

    function event(){
        return $this->belongsTo(Event::class);
    }

    function holds(){
        return $this->hasMany(Hold::class);
    }
}
