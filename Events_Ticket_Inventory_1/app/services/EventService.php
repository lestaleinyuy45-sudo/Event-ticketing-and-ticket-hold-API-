<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Http\Request;

class EventService{
    public function createEvent(Request $request, array $validated){

        $event = $request->user()->events()->create($validated);

        return [
            'event' => $event
        ];
    }

    public function updateEvent(Event $event, array $validated){
            $event = $event->update($validated);

            return [
                'event' => $event
            ];
    }
   
}