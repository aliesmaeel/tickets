<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\TicketResource;
use App\Models\Ticket;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    use ApiResponse;
    public function getTickets(Request $request)
    {
        $filter = $request->input('filter', 'upcoming');
        $customerId = auth()->id();

        $tickets = Ticket::with(['event', 'orderSeat.eventSeat.seatClass'])
            ->where('customer_id', $customerId)
            ->where('status', $filter)
            ->paginate(10);


        $ticketResource=[
          'data'=> TicketResource::collection($tickets->items())->resolve(),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ]
        ];
        return $this->respondValue(
            $ticketResource,
            'Tickets retrieved successfully'
        );
    }


    public function scanTicket(Request $request)
    {

        $request->validate([
            'ticket_code' => 'required|string|exists:tickets,id',
        ]);

        $ticket = Ticket::where('id', $request->ticket_code)->first();

        if (!$ticket) {
            return $this->respondError(message: 'Ticket not found', statusCode: 404);
        }

        if ($ticket->status === 'attended') {
            return $this->respondError(message: 'Ticket already scanned', statusCode: 400);
        }

        $ticket->status = 'attended';
        $ticket->save();

        return $this->respondValue(
            new TicketResource($ticket),
            'Ticket scanned successfully'
        );
    }

    public function getTicket(Request $request)
    {
        $ticketId = $request->id;

        if (!$ticketId) {
            return $this->respondError(message: 'Ticket ID is required', statusCode: 400);
        }

        $ticket = Ticket::with(['event', 'orderSeat.eventSeat.seatClass'])
            ->where('id', $ticketId)
            ->first();

        if (!$ticket) {
            return $this->respondError(message: 'Ticket not found', statusCode: 404);
        }

        return $this->respondValue(
            new TicketResource($ticket),
            'Ticket retrieved successfully'
        );
    }

}
