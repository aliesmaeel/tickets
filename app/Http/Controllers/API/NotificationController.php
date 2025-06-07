<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function sendTestPushNotification(Request $request): JsonResponse
    {
        return FcmService::sendTestPushNotification($request);
    }
}
