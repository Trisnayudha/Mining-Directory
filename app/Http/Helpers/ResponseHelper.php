<?php

namespace App\Http\Helpers;

trait ResponseHelper
{
    protected function sendResponse($message, $payload = null, $status = 200)
    {
        $response = [
            'status' => $status,
            'message' => $message,
            'payload' => $payload
        ];

        return response()->json($response, $status);
    }
}
