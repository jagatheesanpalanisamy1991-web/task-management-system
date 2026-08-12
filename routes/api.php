<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TaskAssignmentRuleController;
use App\Http\Controllers\Api\UserProfileController;

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
    //Route::apiResource('tasks', TaskController::class);
    Route::post('/tasks',[TaskController::class,'store']);
    Route::get('/tasks',[TaskController::class,'index']);
    Route::put('/tasks/{id}',[TaskController::class,'update']);
    Route::get('/tasks/{id}',[TaskController::class,'show']);
    Route::delete('/tasks/{id}',[TaskController::class,'destroy']);
    Route::get('/tasks/{id}/eligible-users', [TaskController::class, 'eligibleUsers']);
    Route::post('/tasks/recompute-eligibility', [TaskController::class, 'recomputeEligibility']);
    Route::apiResource('taskAssignmentRules', TaskAssignmentRuleController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    
    });
Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
    Route::get('/my-eligible-tasks', [TaskController::class, 'myEligibleTasks']);
    Route::put('/user/profile', [UserProfileController::class, 'update']);
    Route::get('/user/profile', [UserProfileController::class, 'show']);
});