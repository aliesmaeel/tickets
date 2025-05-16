<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AdvertisementsController extends Controller
{
    use ApiResponse;

    public function getAds()
    {
        $ads= Advertisement::where('status', 'active')
            ->orderBy('created_at', 'desc')->get();

        if ($ads->isEmpty()) {
            return $this->error('No advertisements found', 404);
        }
        return $this->success($ads, 'Advertisements retrieved successfully');
    }
}
