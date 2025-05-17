<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CityResource;
use App\Models\City;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CityController extends Controller
{
    use ApiResponse;

    public function getCities(Request $request)
    {
        try {
            $cities = CityResource::collection(City::all());

            return $this->respondValue($cities, 'Cities retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Get Cities error:', ['error' => $e->getMessage()]);
            return $this->respondError();
        }
    }
}
