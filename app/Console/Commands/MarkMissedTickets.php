<?php

namespace App\Console\Commands;

use App\Models\OrderSeat;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkMissedTickets extends Command
{
    protected $signature = 'tickets:mark-missed';
    protected $description = 'Mark tickets as missed if their event has ended and the customer did not attend';

    public function handle()
    {
        $now = now();
        $tickets = Ticket::where('status', 'upcoming')
            ->whereHas('event', function ($query) use ($now) {
                $query->where('end_time', '<', $now);
            })->get();


        logger("Marking missed tickets: " . $tickets->count());

        foreach ($tickets as $ticket) {
            $this->info("Ticket ID: {$ticket->id}, Event End Time: {$ticket->event->end_time}");
            $ticket->status = 'missed';
            $ticket->save();
        }

    }
}
