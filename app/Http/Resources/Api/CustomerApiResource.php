<?php

namespace App\Http\Resources\Api;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerApiResource extends JsonResource
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
            'phone' => $this->phone,
            'image' => url('/storage/customers'.$this->image),
            'is_active' => $this->is_active,
            'lang' => $this->lang,
            'wallet' => $this->wallet,
            'birth_date'=>$this->birth_date,
            'gender'=>$this->gender,
            'settings'=>[
                'points_To_money' => '1000,'.ceil(1000 * Setting::getRate('point_to_money_rate')),
            ]
        ];

    }
}
