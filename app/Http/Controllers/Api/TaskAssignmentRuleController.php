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
     * Display a listing of the resource.
     */
    public function index()
    {
        dd(TaskAssignmentRule::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskAssignmentRuleRequest $request)
    {
        $validatedData = $request->validated();
        //dd($validatedData);
        $taskAssignmentRule = TaskAssignmentRule::create($validatedData);
        return response()->json([
            'message' => 'Task assignment rule created successfully.',
            'data' => $taskAssignmentRule,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        dd('show');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskAssignmentRuleRequest $request, string $id)
    {
        dd('update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        dd('destroy');
    }
}
