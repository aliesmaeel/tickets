<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventSeatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name_localized,
            'seat_classes' => $this->seatClasses->map(function ($seatClass) {
                return [
                    'id'    => $seatClass->id,
                    'name'  => $seatClass->name,
                    'color' => $seatClass->color,
                    'price' => $seatClass->price,
                ];
            }),
            'rows_count'   => $this->seats->max('row')+1,
            'cols_count'   => $this->seats->max('col')+1,
            'seats' => $this->seats->map(function ($seat) {
                return [
                    'id'            => $seat->id,
                    'row'           => $seat->row,
                    'col'           => $seat->col,
                    'status'        => $seat->status,
                    'class' => new SeatClassResource($seat->seatClass),
                ];
            }),
        ];
    }
}
