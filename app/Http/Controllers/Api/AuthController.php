<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    // Register a new user
    public function register(RegisterRequest $request): JsonResponse
    {
        $validatedData =  $request->validated();
        //dd($validatedData);
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'] ?? 'user',
            'department' => $validatedData['department'],
            'years_experience' => $validatedData['years_experience'],
            'location' => $validatedData['location'],
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
        
    }
    // Login an existing user
    public function login(LoginRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        //dd($validatedData);
        if(!(Auth::attempt($validatedData))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User logged in successfully.',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);

    }

    // Logout the authenticated user
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    // Get the authenticated user's details
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Profile fetched successfully.',
            'data' => new UserResource($request->user()),
        ], 200);
    }
}
