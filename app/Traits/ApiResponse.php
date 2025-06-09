<?php

namespace App\Traits;



trait ApiResponse
{
    protected function successResponse($data = null, $message = 'OK', $code = 200)
    {
        if ($data instanceof \Illuminate\Http\Resources\Json\JsonResource) {
            $data = $data->response()->getData(true); // get array
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data ?? (object) [],
        ], $code);
    }


    protected function errorResponse($message = 'Something went wrong', $errors = [], $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}
