<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class TicketType extends Model
{
    protected $fillable = [
       'event_id',
        'name',
        'base_price',
        'discount',
        'quantity'
    ];

    protected $appends = ['price'];

    protected function price(): Attribute
    {
        return Attribute::get(function (): int {
            $basePrice = (int) $this->base_price;
            $discount = (int) ($this->discount ?? 0);

            return (int) round($basePrice * (100 - $discount) / 100);
        });
    }

    function event(){
        return $this->belongsTo(Event::class);
    }

    function holds(){
        return $this->hasMany(Hold::class);
    }
}
