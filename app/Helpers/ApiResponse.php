<?php
namespace App\Helpers;

class ApiResponse
{
    public static function successResponse($data = null, $message = '', $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'status_code'=> $status,
            'data' => $data,
        ], $status);
    }

    public static function errorResponse($data = null, $message = 'Error occurred', $status = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'status_code'=> $status,
            'error'=>  $data
        ], $status);
    }
    public static function validationResponse($data = null, $status = 422)
    {
        return response()->json([
            'success' => false,
            'message' => "Validation Error",
            'status_code'=> $status,
            'error'=> $data
        ], $status);
    }
}





?>