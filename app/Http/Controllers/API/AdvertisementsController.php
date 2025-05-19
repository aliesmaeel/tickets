<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Traits\ApiResponse;
use App\Traits\HasLocalizedAttributes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class AdvertisementsController extends Controller
{
    use ApiResponse;

    public function getAds(Request $request)
    {
        $lang = $request->lang;
        App::setLocale($lang);

        $ads = Advertisement::where('active', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($ad) use ($lang) {
                $ad->setLocale($lang);
                return [
                    'id' => $ad->id,
                    'title' => $ad->title_localized,
                    'description' => $ad->description_localized,
                    'link' => $ad->link,
                    'image' => $ad->image,
                ];
            });

        return $this->respondValue(
            ['ads' => $ads],
            __('messages.ads_retrieved_successfully'),
        );
    }

}
