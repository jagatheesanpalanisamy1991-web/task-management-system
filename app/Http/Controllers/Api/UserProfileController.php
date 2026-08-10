<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UpdateProfileRequest;


class UserProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Profile retrieved successfully.',
            'data' => $request->user(),
        ], 200);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();
        $user->update($validated);
        return response()->json([
            'message' => 'Profile updated successfully. Task rules are re-evaluating in the background.',
            'data' => $user,
        ], 200);
    }
}
