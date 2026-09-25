<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\TicketType;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class TicketTypeController extends Controller
{       

        public function index(Request $request, Event $event){
            if($event->organizer_id !== $request->user()->id){
                return response()->json([
                    'message' => 'Unauthorized'
                ],403);
            }

            if($event->status === 'cancelled'){
                return response()->json([
                    'message' => 'Cannot view ticket types for a cancelled event'
                ],422);
            }

            return response()->json([
                'ticket_types' => $event->ticketTypes
            ]);
        }
        public function store(Request $request, Event $event)
{
    if ($event->organizer_id !== $request->user()->id) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    if ($event->status === 'cancelled') {
        return response()->json([
            'message' => 'Cannot create ticket type for a cancelled event'
        ], 422);
    }

    $validated = $request->validate([
        'name' => ['required','string','max:255',Rule::unique('ticket_types')
        ->where(fn ($query) => $query->where('event_id', $event->id))],
        'price' => ['required', 'numeric', 'min:1'],
        'quantity' => ['required', 'integer', 'min:1'],
    ]);

    $ticketType = $event->ticketTypes()->create($validated);

    return response()->json([
        'message' => 'Ticket type successfully created',
        'ticket_type' => $ticketType
    ], 201);
}

            public function update(Request $request, TicketType $ticketType){

                return DB::transaction(function () use ($request, $ticketType) {
                    $ticketType = $ticketType->where('id', $ticketType->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                        
                    if($ticketType->event->organizer_id !== $request->user()->id){
                        return response()->json([
                            'message' => 'Unauthorized'
                        ],403);
                    }

                    if($ticketType->event->status === 'cancelled'){
                        return response()->json([
                            'message' => 'Cannot update ticket type for a cancelled event'
                        ],422);
                    }
                    
                    
                    $activeHolds = $ticketType->holds()->where('status', 'held')
                    ->where('expires_at', '>', now())
                    ->sum('quantity');
                    $confirmedHolds = $ticketType->holds()->where('status','confirmed')->sum('quantity');
                    
                    $committed = $activeHolds +
                                 $confirmedHolds;


                    
                    $validated = $request->validate([
                        'name' => ['sometimes', 'string', 'max:255'],
                        'price' => ['sometimes', 'integer', 'min:1'],
                        'quantity' => ['sometimes', 'integer', 'min:1']
                    ]);

                    if (array_key_exists('quantity', $validated) && $validated['quantity'] < $committed) 
                        {
                        return response()->json([
                            'message' => "quantity cannot be lower than the number 
                                          of holds sold/held ($committed)"
                        ]);
                    }

                    $ticketType->update($validated);

                    return response()->json([
                        'message' => 'Ticket type successfully updated',
                        'ticket_type' => $ticketType
                    ]);
                }
                );
            }

            public function destroy(Request $request, TicketType $ticketType){

                if($ticketType->event->organizer_id !== $request->user()->id){
                    return response()->json([
                        'message' => 'Unauthorized'
                    ],403);
                }

                
                if($ticketType->event->status === 'cancelled'){
                    return response()->json([
                        'message' => 'Cannot delete ticket type for a cancelled event'
                    ],422);
                }

                if ($ticketType->holds()->where('status', 'held')->exists()){
                    return response()->json([
                        'message' => 'Cannot delete ticket type with active holds'
                    ],422);
                }

                if ($ticketType->holds()->where('status', 'confirmed')->exists()){
                    return response()->json([
                        'message' => 'Cannot delete ticket type with confirmed holds'
                    ],422);
                }

                $ticketType->delete();

                return response()->json([
                    'message' => 'Ticket type successfully deleted'
                ]);
            }
        public function inventory(Request $request, Event $event)
{
    if ($event->organizer_id !== $request->user()->id) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    $ticketTypes = $event->ticketTypes()
        ->withSum([
            'holds as sold' => function ($query) {
                $query->where('status', 'confirmed');
            }
        ], 'quantity')
        ->withSum([
            'holds as held' => function ($query) {
                $query->where('status', 'held')
                    ->where('expires_at', '>', now());
            }
        ], 'quantity')
        ->get();

    $ticketTypes->each(function ($ticketType) {
        $ticketType->sold = $ticketType->sold ?? 0;
        $ticketType->held = $ticketType->held ?? 0;

        $ticketType->remaining =
            $ticketType->quantity
            - $ticketType->sold
            - $ticketType->held;
    });

    return response()->json([
        'event' => $event->title,
        'inventory' => $ticketTypes
    ]);
}
}

