<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskAssignmentRule;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;
use App\Http\Requests\StoreTaskAssignmentRuleRequest;
use App\Http\Requests\UpdateTaskAssignmentRuleRequest;

class TaskAssignmentRuleController extends Controller
{
    /**
     * Display a listing of the resource (filtered by task_id if provided).
     */
    public function index(Request $request): JsonResponse
    {
        $query = TaskAssignmentRule::query();

        if ($request->has('task_id')) {
            $query->where('task_id', $request->task_id);
        }

        $rules = $query->get();

        return response()->json([
            'message' => 'Task assignment rules retrieved successfully.',
            'data' => $rules,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskAssignmentRuleRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        
        $taskAssignmentRule = TaskAssignmentRule::create($validatedData);

        return response()->json([
            'message' => 'Task assignment rule created successfully.',
            'data' => $taskAssignmentRule,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $taskAssignmentRule = TaskAssignmentRule::findOrFail($id);

        return response()->json([
            'message' => 'Task assignment rule retrieved successfully.',
            'data' => $taskAssignmentRule,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskAssignmentRuleRequest $request, string $id): JsonResponse
    {
        $taskAssignmentRule = TaskAssignmentRule::findOrFail($id);
        
        $validatedData = $request->validated();
        $taskAssignmentRule->update($validatedData);

        return response()->json([
            'message' => 'Task assignment rule updated successfully.',
            'data' => $taskAssignmentRule,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $taskAssignmentRule = TaskAssignmentRule::findOrFail($id);
        $taskAssignmentRule->delete();

        return response()->json([
            'message' => 'Task assignment rule deleted successfully.',
        ], 200);
    }
}