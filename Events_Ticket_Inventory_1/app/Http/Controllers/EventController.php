<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Hold;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::where('status', 'published')->paginate(15);

        return response()->json([
            'events' => $events
        ]);
    }

    public function publish(Request $request, EventService $eventService)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published,cancelled'],
            'venue' => ['required', 'string', 'max:255'],
            'town' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
        ]);

        $event = $eventService->createEvent($request, $validated);

        return response()->json([
            'message' => 'Event successfully created',
            'event' => $event['event']
        ], 201);
    }

    public function show(Request $request, Event $event)
    {
            
        if ($event->organizer_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'event' => $event
        ]);
    }

    public function update(Request $request, Event $event, EventService $eventService)
    {
        if ($event->organizer_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($event->status === 'cancelled') {
            return response()->json([
                'message' => "You can't edit a cancelled event"
            ], 422);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'venue' => ['sometimes', 'string', 'max:255'],
            'town' => ['sometimes', 'string', 'max:255'],
            'date' => ['sometimes', 'date'],
            'start_time' => ['sometimes', 'date_format:H:i']
        ]);

        $event = $eventService->updateEvent($event, $validated);

        return response()->json([
            'event' => $event['event']
        ]);
    }

   public function cancel(Request $request, Event $event)
{
    if ($event->organizer_id !== $request->user()->id) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    if ($event->status === 'cancelled') {
        return response()->json([
            'message' => 'Event is already cancelled'
        ], 422);
    }

    DB::transaction(function () use ($event) {


    Hold::whereIn('ticket_type_id', $event->ticketTypes()->select('id'))
    ->where('status', 'held')
    ->where('expires_at', '>', now())
    ->lockForUpdate() 
    ->update(['status' => 'released']);

        $event->update([
            'status' => 'cancelled'
        ]);
    });

    return response()->json([
        'message' => 'Event cancelled successfully'
    ]);
}
}