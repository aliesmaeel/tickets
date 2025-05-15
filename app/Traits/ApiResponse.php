<?php

namespace App\Traits;

trait ApiResponse
{
    protected function success($data = [], $message = 'Success', $code = 200)
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'code'    => $code,
            'data'    => $data,
        ], $code);
    }

    protected function error( $data = [] , $message = 'Error' , $code = 400)
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'code'    => $code,
            'data'    => $data,
        ], $code);
    }
}
