<?php

namespace App\Services;

class TicketTypeService
{
    public function createTicketType($event, array $validated): array
    {
        $ticketType = $event->ticketTypes()->create($validated);

        return ['ticket_type' => $ticketType];
    }

    public function updateTicketType($ticketType, array $validated): array
    {
        $ticketType->update($validated);

        return ['ticket_type' => $ticketType];
    }
}