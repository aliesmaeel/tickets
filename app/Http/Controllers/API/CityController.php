<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CityResource;
use App\Models\City;
use App\Traits\ApiResponse;
use App\Traits\HasLocalizedAttributes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class CityController extends Controller
{
    use ApiResponse;

    public function getCities(Request $request)
    {
        App::setLocale(Auth::user()->lang ?? 'en');

        try {
            $cities = CityResource::collection(City::all());

            return $this->respondValue($cities, __('messages.cities_retrieved_successfully'), 200);
        } catch (\Exception $e) {
            logger()->error('Get Cities error:', ['error' => $e->getMessage()]);
            return $this->respondError();
        }
    }
}
