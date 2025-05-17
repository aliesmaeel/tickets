<?php

namespace App\Http\Resources\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            // 'type' => $this->type,
            'image' => $this->image ?? asset('assets/images/default_event.jpg'),
            'address' => $this->address,
            'address_link' => $this->address_link,
            'start_date' => Carbon::parse($this->start_time)->format('Y-m-d H:i:s'),
            'end_date' => Carbon::parse($this->end_time)->format('Y-m-d H:i:s'),
            'display_start_date' => Carbon::parse($this->display_start_date)->format('Y-m-d H:i:s'),
            'display_end_date' => Carbon::parse($this->display_end_date)->format('Y-m-d H:i:s'),
            'city_id' => $this->city_id,
            'category_id' => $this->category_id,
            'city_name' => $this->city?->name,
            'category_name' => $this->category?->name,
        ];
    }
}
