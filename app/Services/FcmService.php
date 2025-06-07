<?php

namespace App\Services;

use App\Dtos\Fcm\FcmDto;
use App\Dtos\Fcm\FcmReceiverDto;
use App\Enums\UserType;
use App\Models\FcmToken;
use App\Models\FcmTopic;
use App\Models\FcmTopicSubscription;
use Google\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FcmService
{
    public static function sendPushNotification(FcmDto $fcmDto): JsonResponse
    {
        $fcmTokens = self::getFcmTokens($fcmDto->receivers);

        if ($fcmTokens->isEmpty()) {
            return self::response(false, 'No device token found');
        }

        $projectId = config('services.fcm.project_id');
        $credentialsFilePath = config('services.fcm.credentials_file_path');

        if (empty($projectId)) {
            return self::response(false, 'Project ID not found');
        }

        if (! Storage::exists($credentialsFilePath)) {
            return self::response(false, 'Credentials file not found');
        }

        try {
            $accessToken = self::getAccessToken($credentialsFilePath);

            if (empty($accessToken)) {
                return self::response(false, 'Access token has not been generated. Please check the credentials file');
            }

            $response = self::sendNotification($fcmTokens, $fcmDto, $accessToken, $projectId);

            if (isset($response['error'])) {
                return self::response(false, 'Error: '.$response['error']['message'], $response, 500);
            }

            return self::response(true, 'Notifications have been sent', $response);
        } catch (\Exception $e) {
            return self::response(false, 'Error: '.$e->getMessage(), null, 500);
        }
    }

    private static function getFcmTokens(array $receivers)
    {
        return FcmToken::query()
            ->whereIn('userable_id', array_map(fn ($receiver) => $receiver->id, $receivers))
            ->whereIn('userable_type', array_map(fn ($receiver) => UserType::getPathFromString($receiver->type), $receivers))
            ->where('is_active', 1)
            ->select('fcm_token')
            ->get()
            ->unique('fcm_token');
    }

    private static function getAccessToken(string $credentialsFilePath): ?string
    {
        $client = new Client;
        $client->setAuthConfig(Storage::path($credentialsFilePath));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $token = $client->fetchAccessTokenWithAssertion();

        return $token['access_token'] ?? null;
    }

    private static function sendNotification($fcmTokens, FcmDto $fcmDto, string $accessToken, string $projectId)
    {
        $tokens = $fcmTokens->pluck('fcm_token')->toArray();
        $sendMethod = self::prepareSendMethod($tokens, $accessToken);

        $extraData = $fcmDto->data ?? [];

        if (! empty($extraData)) {
            // Remove nulls and cast all values to string
            $extraData = array_filter($extraData, fn ($v) => ! is_null($v));
            $extraData = array_map('strval', $extraData);
            $sendMethod['data'] = $extraData;
        }

        return Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
            'Content-Type' => 'application/json',
        ])->post("https://fcm.googleapis.com/v1/projects/$projectId/messages:send", [
            'validate_only' => false, // Flag for testing the request without actually delivering the notification.
            'message' => [
                ...$sendMethod,
                'notification' => [
                    'title' => $fcmDto->title,
                    'body' => $fcmDto->body,
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $fcmDto->title,
                                'subtitle' => $fcmDto->subtitle,
                                'body' => $fcmDto->body,
                            ],
                        ],
                    ],
                ],
            ],
        ])->json();
    }

    private static function prepareSendMethod(array $tokens, string $accessToken): array
    {
        if (count($tokens) > 1) {
            $topic = 'group_'.uniqid();
            $chunks = array_chunk($tokens, 1000);

            $fcmTopic = FcmTopic::create(['topic' => $topic]);

            foreach ($chunks as $chunk) {
                Http::withHeaders([
                    'Authorization' => "Bearer $accessToken",
                    'Content-Type' => 'application/json',
                    'access_token_auth' => 'true',
                ])->post('https://iid.googleapis.com/iid/v1:batchAdd', [
                    'to' => "/topics/$topic",
                    'registration_tokens' => $chunk,
                ]);

                FcmTopicSubscription::insert(
                    array_map(fn ($token) => [
                        'fcm_topic_id' => $fcmTopic->id,
                        'fcm_token' => $token,
                    ], $chunk)
                );
            }

            return ['topic' => $topic];
        }

        return ['token' => $tokens[0]];
    }

    private static function response(bool $success, string $message, $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function sendTestPushNotification(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'receiver_token' => 'required|string',
            'title' => 'required|string',
            'subtitle' => 'nullable|string',
            'body' => 'required|string',
            'data' => 'nullable|array',
        ]);

        $fcmTokens = collect([['fcm_token' => $validated['receiver_token']]]);

        $projectId = config('services.fcm.project_id');
        $credentialsFilePath = config('services.fcm.credentials_file_path');

        if (empty($projectId)) {
            return self::response(false, 'Project ID not found');
        }

        if (! Storage::exists($credentialsFilePath)) {
            return self::response(false, 'Credentials file not found');
        }

        try {
            $accessToken = self::getAccessToken($credentialsFilePath);

            if (empty($accessToken)) {
                return self::response(false, 'Access token has not been generated. Please check the credentials file');
            }

            $fcmDto = new FcmDto(
                receivers: new FcmReceiverDto(id: 1, type: UserType::Customer->value),
                title: $validated['title'],
                subtitle: $validated['subtitle'] ?? '',
                body: $validated['body'] ?? '',
                data: $validated['data'] ?? [],
            );

            $response = self::sendNotification($fcmTokens, $fcmDto, $accessToken, $projectId);

            if (isset($response['error'])) {
                return self::response(false, 'Error: '.$response['error']['message'], $response, 500);
            }

            return self::response(true, 'Notifications have been sent', $response);
        } catch (\Exception $e) {
            return self::response(false, 'Error: '.$e->getMessage(), null, 500);
        }
    }
}
