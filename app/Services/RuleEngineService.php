<?php

namespace App\Services;

use App\Models\TaskAssignmentRule;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RuleEngineService
{
    private const NUMERIC_ATTRIBUTES = ['minimum_experience', 'maximum_active_tasks'];
    private const ATTRIBUTE_COLUMN_MAP = [
        'department' => 'department',
        'minimum_experience' => 'years_experience',
        'location' => 'location',
        // 'maximum_active_tasks' => 'active_tasks_count',
    ];
 
    public function findEligibleUser(Task $task): ?User
    {
        $conditions = $task->taskRules()->get(); // Fetch the associated rules for the task
 
        if ($conditions->isEmpty()) {
            return null;
        }
        Log::info('test');
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

        foreach($users as $user)
        {
         if ($this->userMatchesAllRules($user, $conditions)) {
            return $user;
         }   
        }
        return null;
    }
    public function evaluateUser(User $user): void
    {
        Log::info('Evaluate User method running..');
        Log::info('Evaluate User started', [
            'user_id' => $user->id,
        ]);
        // $rules = TaskAssignmentRule::query()
        //         ->where(function ($query) use ($user){
        //             $query->where
        //         });
        // ;
        Task::query()
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
                Log::info("Task {$task->id} assigned to user {$user->id} after profile update.");

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
        Log::info($task);
        Log::info($task->taskRules);
        $task->load('taskRules');
        if($task->taskRules->isEmpty())
        {
            return;
        }
        /**
         * Task already having an assignee
         * check whether the assignee still matches
         * with the task rules
         */
        if(!$task->assignment_pending && $task->assigned_to)
        {
            $currentUser = User::find($task->assigned_to);
            if ($currentUser && $this->userMatchesAllRules($currentUser, $task->taskRules)) {
                Log::info('Current User still matches the rules..');
                return;
            }
        }
        /**
         * Current assigned user not eligible 
         * check the new eligible user matching with the rules
         */
        $user = $this->findEligibleUser($task);
        if(!$user)
        {
            Log::info("No eligible user found for task {$task->id}");
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

        Log::info("Task {$task->id} Assigned to {$user->id}");

    }
    private function userMatchesAllRules(User $user,Collection $rules): bool
    {
        Log::info("Evaluating the user {$user->id} and rules {$rules}");
        foreach ($rules as $rule) {
            if ($rule->rule_attribute === 'maximum_active_tasks') {
                if (!$this->userMatchesActiveTaskRule($user, $rule)) {
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
        return Task::query()
            ->where('assigned_to', $user->id)
            ->whereIn('status', ['todo', 'in_progress'])
            ->count();
    }
    private function userMatchesActiveTaskRule(User $user,TaskAssignmentRule $rule){
        $activeTasksCount = $this->getActiveTasksCount($user);
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
        Log::info('Active task rule evaluation', [
            'user_id' => $user->id,
            'active_tasks' => $activeTasksCount,
            'operator' => $rule->rule_operator,
            'rule_value' => $ruleValue,
            'matched' => $result,
        ]);
        return $result;
    }
}