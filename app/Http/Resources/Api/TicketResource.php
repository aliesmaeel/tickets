<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userLang = $request->user()?->lang ?? 'en';
        return [
            'id' => $this->id,
            'status' => $this->status,
            'event_name' => $this->event?->name[$userLang],
            'address' => $this->event?->address[$userLang],
            'date' => optional($this->event?->start_time)->format('Y-m-d'),
            'time' => optional($this->event?->start_time)->format('H:i') . ' - ' . optional($this->event?->end_time)->format('H:i'),
            'row' => $this->orderSeat?->eventSeat?->row,
            'col' => $this->orderSeat?->eventSeat?->col,
            'ticketType' => $this->orderSeat?->eventSeat?->seatClass?->name,
            'price' => $this->orderSeat?->eventSeat?->seatClass?->price,
            'place_order_time_left' => (int) max(0, now()->diffInMinutes($this->created_at->addMinutes($this->event?->time_to_place_cache_order), false))+1,

        ];
    }
}
