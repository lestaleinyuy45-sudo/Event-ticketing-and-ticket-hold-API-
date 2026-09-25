<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{use HasFactory;
    protected $fillable = [
            'organizer_id',
            'title',
            'status',
            'venue',
            'town',
            'description',
            'date',
            'start_time',
    ];

    function organizer() {
        return $this->belongsTo(User::class);
    }
    function ticketTypes(){
        return $this->hasMany(TicketType::class);
    }
}
