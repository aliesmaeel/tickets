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
            'name' => $this->name_localized,
            'description' => $this->description_localized,
            // 'type' => $this->type,
            'image' => url('storage/'.$this->image) ?? null,
            'address' => $this->address_localized,
            'address_link' => $this->address_link,
            'address_image' => url('storage/'.$this->address_image),
            'start_date' => Carbon::parse($this->start_time)->format('Y-m-d H:i:s'),
            'end_date' => Carbon::parse($this->end_time)->format('Y-m-d H:i:s'),
            'display_start_date' => Carbon::parse($this->display_start_date)->format('Y-m-d H:i:s'),
            'display_end_date' => Carbon::parse($this->display_end_date)->format('Y-m-d H:i:s'),
            'city' => new CityResource($this->city),
            'category' => new CategoryResource($this->category),
        ];
    }
}
