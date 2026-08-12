<?php

namespace App\Services;

use App\Models\TaskAssignmentRule;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
class RuleEngineService
{
    private const NUMERIC_ATTRIBUTES = ['minimum_experience', 'maximum_active_tasks'];
    private const ATTRIBUTE_COLUMN_MAP = [
        'department' => 'department',
        'minimum_experience' => 'years_experience',
        'location' => 'location',
        // 'maximum_active_tasks' => 'active_tasks_count',
    ];
    private const ACTIVE_TASKS_CACHE_TTL = 60;

    public function findEligibleUser(Task $task,?Collection $conditions = null): ?User
    {
        //$conditions = $task->taskRules()->get(); // Fetch the associated rules for the task

        if ($conditions->isEmpty()) {
            return null;
        }
        //Log::info('test');
        $query = User::query()->where('role', 'user');
 
        foreach ($conditions as $rule) {

            if ($rule->rule_attribute === 'maximum_active_tasks') {
                continue;
            }

            $column = self::ATTRIBUTE_COLUMN_MAP[$rule->rule_attribute] ?? null;
 
            if (! $column) {
                continue; // unknown attribute label - skip rather than error
            }
 
            $value = in_array($rule->rule_attribute, self::NUMERIC_ATTRIBUTES, true)
                ? (int) $rule->rule_value
                : $rule->rule_value;
 
            $query->where($column, $rule->rule_operator, $value);
        }
 
        $users = $query
            ->orderBy('id', 'asc')
            ->get();
        $activeTaskCounts = $this->getActiveTasksCountBatch($users->pluck('id')->all());
        foreach($users as $user)
        {
        //  if ($this->userMatchesAllRules($user, $conditions)) {
        //     return $user;
        //  } 
         if ($this->userMatchesAllRules($user, $conditions, $activeTaskCounts[$user->id] ?? 0)) {
            return $user;
        }  
        }
        return null;
    }
    public function evaluateUser(User $user): void
    {
        Log::info('Evalaute User');
        Task::query()
            ->where('assigned_to', $user->id)
            ->whereIn('status', ['todo', 'in_progress'])
            ->with('taskRules')
            ->chunkById(500, function ($tasks) use ($user) {
                foreach ($tasks as $task) {

                    if (!$this->userMatchesAllRules($user, $task->taskRules)) {

                        // Current user is no longer eligible.
                        // Don't immediately assign another user here yet.

                        $task->update([
                            'assigned_to' => null,
                            'assignment_pending' => true,
                        ]);
                    }
                }
            });
        $taskIds = TaskAssignmentRule::query()
                    ->select('task_id')
                    ->where('rule_operator','=')
                    ->where(function ($query) use ($user) {
                        $query
                            ->where(function ($q) use ($user) {
                                $q->where('rule_attribute', 'department')
                                ->where('rule_value', $user->department);
                            })
                            ->orWhere(function ($q) use ($user) {
                                $q->where('rule_attribute', 'location')
                                ->where('rule_value', $user->location);
                            })
                            ->orWhere(function ($q) use ($user) {
                                $q->where('rule_attribute', 'minimum_experience')
                                ->where('rule_value', $user->years_experience);
                            });
                    })
                    ->groupBy('task_id')
                    ->havingRaw('COUNT(DISTINCT rule_attribute) = 3')
                    ->pluck('task_id');
        //Log::info("Tasks {$taskIds}");
        if ($taskIds->isEmpty()) {
            //Log::info("No candidate tasks found for user {$user->id}");
            return;
        }
        Task::query()
        ->whereIn('id',$taskIds)
        ->where('assignment_pending',true)
        ->with('taskRules')
        ->chunkById(500, function ($tasks) use ($user) {
            foreach ($tasks as $task) {
                $conditions = $task->taskRules;

                if ($conditions->isEmpty()) {
                    continue;
                }

                // foreach ($conditions as $rule) {
                //     if (!$this->userMatchesRule($user, $rule)) {
                //         $matchesAllRules = false;
                //         break;
                //     }

                //     // $column = self::ATTRIBUTE_COLUMN_MAP[$rule->rule_attribute] ?? null;

                //     // if (! $column) {
                //     //     $matchesAllRules = false;
                //     //     break;
                //     // }

                //     // $userValue = $user->{$column} ?? null;

                //     // if ($userValue === null) {
                //     //     $matchesAllRules = false;
                //     //     break;
                //     // }

                //     // $ruleValue = in_array($rule->rule_attribute, self::NUMERIC_ATTRIBUTES, true)
                //     //     ? (int) $rule->rule_value
                //     //     : $rule->rule_value;

                //     // // Evaluate condition using the rule operator
                //     // $conditionMet = match ($rule->rule_operator) {
                //     //     '=' => strcasecmp(trim((string) $userValue), trim((string) $ruleValue)) === 0,
                //     //     '!=' => strcasecmp(trim((string) $userValue), trim((string) $ruleValue)) !== 0,
                //     //     '>=' => (float) $userValue >= (float) $ruleValue,
                //     //     '<=' => (float) $userValue <= (float) $ruleValue,
                //     //     '>' => (float) $userValue > (float) $ruleValue,
                //     //     '<' => (float) $userValue < (float) $ruleValue,
                //     //     default => false,
                //     // };

                //     // if (! $conditionMet) {
                //     //     $matchesAllRules = false;
                //     //     break;
                //     // }
                // }
                //     if (!$matchesAllRules) {
                //     continue;
                // }

                if (!$this->userMatchesAllRules($user, $conditions)) {
                    continue;
                }
                $task->update([
                    'assigned_to' => $user->id,
                    'assignment_pending' => false,
                    'status' => 'todo'
                ]);
                //Log::info("Task {$task->id} assigned to user {$user->id} after profile update.");

            }
        });
    }

    private function userMatchesRule(User $user, TaskAssignmentRule $rule): bool
    {
        $column = self::ATTRIBUTE_COLUMN_MAP[$rule->rule_attribute] ?? null;

        if (!$column) {
            return false;
        }

        $userValue = $user->{$column} ?? null;

        if ($userValue === null) {
            return false;
        }

        $ruleValue = in_array(
            $rule->rule_attribute,
            self::NUMERIC_ATTRIBUTES,
            true
        )
            ? (int) $rule->rule_value
            : $rule->rule_value;

        return match ($rule->rule_operator) {

            '=' => strcasecmp(
                trim((string) $userValue),
                trim((string) $ruleValue)
            ) === 0,

            '!=' => strcasecmp(
                trim((string) $userValue),
                trim((string) $ruleValue)
            ) !== 0,

            '>=' => (float) $userValue >= (float) $ruleValue,

            '<=' => (float) $userValue <= (float) $ruleValue,

            '>' => (float) $userValue > (float) $ruleValue,

            '<' => (float) $userValue < (float) $ruleValue,

            default => false,
        };
    }
    public function evaluateTask(Task $task): void
    {
        //Log::info($task);
        //Log::info($task->taskRules);
        //$task->load('taskRules');
        $conditions = $task->taskRules;
        // if($task->taskRules->isEmpty())
        // {
        //     return;
        // }
        if ($conditions->isEmpty()) {
            return;
        }
        /**
         * Task already having an assignee
         * check whether the assignee still matches
         * with the task rules
         */
        if(!$task->assignment_pending && $task->assigned_to)
        {
            //$currentUser = User::select(['id','department','location','years_experience'])->find($task->assigned_to);
            $currentUser = $task->assignee;
            if ($currentUser) {
                //$start = microtime(true);
                $count = $this->getActiveTasksCount($currentUser);
                // Log::info('getActiveTasksCount', [
                //     'task_id' => $task->id,
                //     'user_id' => $currentUser->id,
                //     'seconds' => microtime(true) - $start,
                // ]);
                //$start = microtime(true);
                if ($this->userMatchesAllRules($currentUser, $conditions, $count)) {
                    // Log::info('userMatchesAllRules', [
                    //     'task_id' => $task->id,
                    //     'user_id' => $currentUser->id,
                    //     'seconds' => microtime(true) - $start,
                    // ]);
                    return;
                }
                
            }
        }
        /**
         * Current assigned user not eligible 
         * check the new eligible user matching with the rules
         */
        //$start = microtime(true);
        $user = $this->findEligibleUser($task,$conditions);
        // Log::info('findEligibleUser', [
        //     'task_id' => $task->id,
        //     'seconds' => microtime(true) - $start,
        // ]);
        if(!$user)
        {
            //Log::info("No eligible user found for task {$task->id}");
            $task->update([
                'assigned_to' => null,
                'assignment_pending' => true,
            ]);
            return;
        }
        $task->update([
            'assigned_to' => $user->id,
            'assignment_pending' => false,
            'status' => 'todo',
        ]);

        //Log::info("Task {$task->id} Assigned to {$user->id}");

    }
    private function userMatchesAllRules(User $user,Collection $rules,?int $activeTasksCount = null): bool
    {
        //Log::info("Evaluating the user {$user->id} and rules {$rules}");
        foreach ($rules as $rule) {
            if ($rule->rule_attribute === 'maximum_active_tasks') {
                $count = $activeTasksCount ?? $this->getActiveTasksCount($user);
                if (!$this->userMatchesActiveTaskRule($count, $rule)) {
                    return false;
                }

                continue;
            }

            if (!$this->userMatchesRule($user, $rule)) {
                return false;
            }
        }
        return true;
    }
    private function getActiveTasksCount(User $user): int
    {
        $cacheKey = "user:{$user->id}:active_tasks_count";
        // return Task::query()
        //     ->where('assigned_to', $user->id)
        //     ->whereIn('status', ['todo', 'in_progress'])
        //     ->count();
        //Log::info($cacheKey);
        return Cache::remember($cacheKey, 60, function () use ($user){
            return Task::query()
            ->where('assigned_to', $user->id)
            ->whereIn('status', ['todo', 'in_progress'])
            ->count();
        });
    }
    private function userMatchesActiveTaskRule(int $activeTasksCount,TaskAssignmentRule $rule){
        //$activeTasksCount = $this->getActiveTasksCount($user);

        $ruleValue = (int) $rule->rule_value;
        $result = match ($rule->rule_operator) {
            '='  => $activeTasksCount === $ruleValue,
            '!=' => $activeTasksCount !== $ruleValue,
            '>=' => $activeTasksCount >= $ruleValue,
            '<=' => $activeTasksCount <= $ruleValue,
            '>'  => $activeTasksCount > $ruleValue,
            '<'  => $activeTasksCount < $ruleValue,
            default => false,
        };
        return $result;
    }
    public function getAllEligibleUsers(Task $task) :Collection
    {
        $conditions = $task->taskRules()->get();
        if ($conditions->isEmpty()) {
            return collect();
        }
        $query = User::query()->where('role', 'user');
        foreach ($conditions as $rule) {
            if ($rule->rule_attribute === 'maximum_active_tasks') {
                continue;
            }
            $column = self::ATTRIBUTE_COLUMN_MAP[$rule->rule_attribute] ?? null;
            if (! $column) {
                continue;
            }
            $value = in_array($rule->rule_attribute, self::NUMERIC_ATTRIBUTES, true)
                ? (int) $rule->rule_value
                : $rule->rule_value;
            $query->where($column, $rule->rule_operator, $value);
        }
        $users = $query->orderBy('id','asc')->get();
        $activeTaskCounts = $this->getActiveTasksCountBatch($users->pluck('id')->all());

        // return $query->orderBy('id', 'asc')->get()
        //     ->filter(fn (User $user) => $this->userMatchesAllRules($user, $conditions))
        //     ->values();
        return $users->filter(function (User $user) use ($conditions, $activeTaskCounts){
                return $this->userMatchesAllRules(
            $user,
            $conditions,
            $activeTaskCounts[$user->id] ?? 0);
        })->values();
        

    }

    public function recomputeForTasks(array $taskIds = []): array
    {
        //Log::info($taskIds);
        $processed = 0;
        $failed = [];
        $query = !empty($taskIds)
        ? Task::whereIn('id', $taskIds)
        : Task::whereIn('status', ['todo', 'in_progress']);

        $query->with(['taskRules','assignee:id,department,location,years_experience'])->chunkById(500, function ($tasks) use (&$processed, &$failed) {
            foreach ($tasks as $task) {
                try {
                $this->evaluateTask($task);
                $processed++;
                } catch (\Throwable $e) {
                    $failed[] = $task->id;
                    //Log::error("Eligibility recompute failed for task {$task->id}: {$e->getMessage()}");
                }
            }
        });
        return [
            'processed' => $processed,
            'failed'    => $failed,
        ];
 
    }
    private function getActiveTasksCountBatch(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        // One grouped query for ALL candidates, instead of N separate calls
        return Task::query()
            ->select('assigned_to', DB::raw('COUNT(*) as active_count'))
            ->whereIn('assigned_to', $userIds)
            ->whereIn('status', ['todo', 'in_progress'])
            ->groupBy('assigned_to')
            ->pluck('active_count', 'assigned_to')
            ->toArray();
    }
}