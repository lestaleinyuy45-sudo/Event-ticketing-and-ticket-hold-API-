<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TicketType;

class Hold extends Model
{
    protected $fillable = [
        'ticket_type_id',
        'quantity',
        'status',
        'token',
        'expires_at'
    ];

    function ticketType(){
        return $this->belongsTo(TicketType::class);
    }

    
}

