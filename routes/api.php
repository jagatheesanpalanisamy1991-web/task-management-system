<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
});
// Admin-only routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/test', function () {
        return response()->json(['message' => 'Welcome Admin user!'], 200);
    });
});
