<?php
namespace App\Traits;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
trait ApiResponse
{
    public function successResponse(int $statusCode = 200,string $message = null, mixed $data=null,$errors = null): JsonResponse
    {
        return Response::json([
            'status' => true,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
        ], $statusCode);
    }
    public function errorResponse(string $message, int $statusCode = 400, mixed $data=null, mixed $errors=null): JsonResponse
    {
        return Response::json([
            'status' => false,
            'message' => $message,
            'data' => $data,
            'errors' => $errors ?? null,
        ], $statusCode);
    }
}