<?php

namespace App\Traits;

trait HttpResponses
{
    protected function success($data, $message = null, $code = 200)
    {
        return response()->json([
            "status" => "Request was successful.",
            "message" => $message,
            "data" => $data
        ], $code);
    }

    protected function error($data, $message = null, $code)
    {
        return response()->json([
            "status" => "Error has occurred...",
            "message" => $message,
            "data" => $data
        ], $code);
    }

    protected function exception($e)
    {
        $response = [
            "status" => "Error has occurred...",
            "message" => $e->getMessage(),
        ];
        $code = 500;
        
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            $code = 422;
            $response['errors'] = $e->validator->errors();
        } else if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            $code = 404;
        }

        return response()->json($response, $code);
    }
}
