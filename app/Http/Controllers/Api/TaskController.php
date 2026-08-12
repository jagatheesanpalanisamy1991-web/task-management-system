<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Models\User;
use App\Traits\ApiResponse;
use Whoops\Run;
use App\Services\RuleEngineService;

class TaskController extends Controller
{
    use ApiResponse;

    public function __construct(private RuleEngineService $ruleEngine) {}
    /**
     * Display a listing of the resource.
     */
    
    public function index(Request $request)
    {
        $tasks = Task::with([
            'createdBy'
        ])->where('created_by',$request->user()->id)->latest()->paginate(10);
        return response()->json([
            'message' => 'Tasks fetched successfully.',
            'data' => $tasks,
            'pagination' => [
            'current_page' => $tasks->currentPage(),
            'per_page' => $tasks->perPage(),
            'total' => $tasks->total(),
            'last_page' => $tasks->lastPage(),
        ],
        ], 200);
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        $validatedData = $request->validated();
        //dd($validatedData);
        $validatedData['created_by'] = $request->user()->id;
        $validatedData['assignment_pending'] = true;
        $task = Task::create($validatedData);
        return response()->json([
            'message' => 'Task created successfully.',
            'data' => $task,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        return response()->json([
            'message' => 'Task fetched successfully.',
            'data' => $task,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $validatedData = $request->validated();
        //dd($validatedData);
        $task->update($validatedData);
        return response()->json( [  
            'message' => 'Task updated successfully.',
            'data' => $task,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['message' => 'Task deleted successfully.'], 200);
    }
    public function myEligibleTasks(Request $request)
    {
        $user = $request->user();
        //$eligibleTasks = Task::latest()->paginate(10);
        $eligibleTasks = Task::query()
            ->where('assigned_to',$user->id)
            ->paginate(10);
        return response()->json([
            'message' => 'Eligible fetched successfully.',
            'data' => $eligibleTasks,
            'pagination' => [
            'current_page' => $eligibleTasks->currentPage(),
            'per_page' => $eligibleTasks->perPage(),
            'total' => $eligibleTasks->total(),
            'last_page' => $eligibleTasks->lastPage(),
        ],
        ], 200);
    }
    public function eligibleUsers($id){
        $task = Task::findOrFail($id);
        $users = $this->ruleEngine->getAllEligibleUsers($task);
        return response()->json([
            'task_id' => $task->id,
            'count'   => $users->count(),
            'data'    => $users,
        ]);
    }
    public function recomputeEligibility(Request $request)
    {
        $taskIds = $request->task_ids??[];
        $taskIds = $taskIds?array_unique($taskIds):[];
        $result = $this->ruleEngine->recomputeForTasks($taskIds);
        return response()->json([
            'message'         => 'Eligibility recomputed',
            'scope'           => !empty($taskIds) ? 'selected' : 'all_open',
            'tasks_processed' => $result['processed'],
            'tasks_failed'    => $result['failed'],
        ]);
    }
}
