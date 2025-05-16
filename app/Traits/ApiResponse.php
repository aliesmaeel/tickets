<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\DataCollection;
use Error;
use Exception;
trait ApiResponse
{
//    protected function success($data = [], $message = 'Success', $code = 200)
//    {
//        return response()->json([
//            'status'  => true,
//            'message' => $message,
//            'code'    => $code,
//            'data'    => $data,
//        ], $code);
//    }
//
//    protected function error( $data = [] , $message = 'Error' , $code = 400)
//    {
//        return response()->json([
//            'status'  => false,
//            'message' => $message,
//            'code'    => $code,
//            'data'    => $data,
//        ], $code);
//    }



    protected function respondWithResource(JsonResource $resource, $message = null, $statusCode = 200, $headers = [])
    {
        return $this->apiResponse(
            [
                'success' => true,
                'data' => $resource,
                'message' => $message,
            ], $statusCode, $headers
        );
    }

    protected function respondWithCollectionOrArray(Collection|array $resource, $message = null, $statusCode = 200, $headers = [])
    {
        return $this->apiResponse(
            [
                'success' => true,
                'data' => $resource,
                'message' => $message,
            ], $statusCode, $headers
        );
    }

    protected function apiResponse($data = [], $statusCode = 200, $headers = [])
    {

        $result = $this->parseGivenData($data, $statusCode, $headers);

        return response()->json(
            $result['content'], $result['statusCode'], $result['headers']
        );
    }

    public function parseGivenData($data = [], $statusCode = 200, $headers = [])
    {
        $responseStructure = [
            'success' => $data['success'],
            'message' => $data['message'] ?? null,
            'data' => $data['data'] ?? null,
        ];
        if (isset($data['errors'])) {
            $responseStructure['errors'] = $data['errors'];
        }
        if (isset($data['status'])) {
            $statusCode = $data['status'];
        }

        if (isset($data['exception']) && ($data['exception'] instanceof Error || $data['exception'] instanceof Exception)) {
            if (config('app.env') !== 'production') {
                $responseStructure['exception'] = [
                    'message' => $data['exception']->getMessage(),
                    'file' => $data['exception']->getFile(),
                    'line' => $data['exception']->getLine(),
                    'code' => $data['exception']->getCode(),
                    'trace' => $data['exception']->getTrace(),
                ];
            }

            if ($statusCode === 200) {
                $statusCode = 500;
            }
        }
        if ($data['success'] === false) {
            if (isset($data['error_code'])) {
                $responseStructure['error_code'] = $data['error_code'];
            } else {
                $responseStructure['error_code'] = 1;
            }
        }

        return ['content' => $responseStructure, 'statusCode' => $statusCode, 'headers' => $headers];
    }

    protected function respondWithResourceCollection(ResourceCollection $resourceCollection, $message = null, $statusCode = 200, $headers = [])
    {
        return $this->apiResponse(
            [
                'success' => true,
                'data' => $resourceCollection,
                'message' => $message,
            ], $statusCode, $headers
        );
    }

    protected function respondWithDataCollection(DataCollection $resourceCollection, $message = null, $statusCode = 200, $headers = [])
    {
        return $this->apiResponse(
            [
                'success' => true,
                'data' => $resourceCollection,
                'message' => $message,
            ], $statusCode, $headers
        );
    }

    protected function respondSuccess($message = '')
    {
        return $this->apiResponse(['success' => true, 'message' => $message]);
    }

    protected function respondValue($data, $message = '', $statusCode = 200, $headers = [])
    {
        return $this->apiResponse(
            [
                'success' => true,
                'data' => $data,
                'message' => $message,
            ], $statusCode, $headers
        );
    }

    protected function respondRetrivedData($data, $message = null, $statusCode = 200, $headers = [])
    {
        return $this->apiResponse(
            [
                'success' => true,
                'data' => $data,
                'message' => $message ?? 'Data Retrieved Successfully',
            ], $statusCode, $headers
        );
    }

    protected function respondCreated($data, $message)
    {
        return $this->apiResponse([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], 201);
    }

    protected function respondNoContent($message = 'No Content Found')
    {
        return $this->apiResponse(['success' => false, 'message' => $message], 200);
    }

    protected function respondNoContentResource($message = 'No Content Found')
    {
        // return $this->respondWithResource(new EmptyResource([]), $message);
    }

    protected function respondNoContentResourceCollection($message = 'No Content Found')
    {
        // return $this->respondWithResourceCollection(new EmptyCollection([]), $message);
    }

    protected function respondUnAuthorized($message = 'Unauthorized')
    {
        return $this->respondError($message, null, 401);
    }

    protected function respondError($message = null, ?Exception $exception = null, int $statusCode = 400, int $error_code = 1): JsonResponse
    {
        // report($exception);
        return $this->apiResponse(
            [
                'success' => false,
                'message' => $message ?? 'There was an internal error, Pls try again later',
                'exception' => $exception,
                'error_code' => $error_code,
            ], $statusCode
        );
    }

    protected function respondForbidden($message = 'Forbidden')
    {
        return $this->respondError($message, null, 403);
    }

    protected function respondNotFound($message = 'Not Found')
    {
        return $this->respondError($message, null, 404);
    }

    protected function respondInternalError($message = 'Internal Error')
    {
        return $this->respondError($message, null, 500);
    }

    protected function respondExceptionError(ValidationException $exception)
    {
        return $this->apiResponse(
            [
                'success' => false,
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ],
            422
        );
    }

    protected function respondValidationErrors(array $errors, int $code = 422)
    {
        return $this->apiResponse(
            [
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $errors,
            ],
            $code
        );
    }


}
