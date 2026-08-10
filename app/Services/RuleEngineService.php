<?php

namespace App\Services;

use App\Models\TaskAssignmentRule;
use App\Models\Task;
use App\Models\User;
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
            $column = self::ATTRIBUTE_COLUMN_MAP[$rule->rule_attribute] ?? null;
 
            if (! $column) {
                continue; // unknown attribute label - skip rather than error
            }
 
            $value = in_array($rule->rule_attribute, self::NUMERIC_ATTRIBUTES, true)
                ? (int) $rule->rule_value
                : $rule->rule_value;
 
            $query->where($column, $rule->rule_operator, $value);
        }
 
        return $query
            ->orderBy('id', 'asc')
            ->first();
    }
    public function evaluateUser(User $user): void
    {
        Task::with('taskRules')->chunkById(500, function ($tasks) use ($user) {
            foreach ($tasks as $task) {
                $conditions = $task->taskRules;

                if ($conditions->isEmpty()) {
                    continue;
                }

                $matchesAllRules = true;

                foreach ($conditions as $rule) {
                    $column = self::ATTRIBUTE_COLUMN_MAP[$rule->rule_attribute] ?? null;

                    if (! $column) {
                        $matchesAllRules = false;
                        break;
                    }

                    $userValue = $user->{$column} ?? null;

                    if ($userValue === null) {
                        $matchesAllRules = false;
                        break;
                    }

                    $ruleValue = in_array($rule->rule_attribute, self::NUMERIC_ATTRIBUTES, true)
                        ? (int) $rule->rule_value
                        : $rule->rule_value;

                    // Evaluate condition using the rule operator
                    $conditionMet = match ($rule->rule_operator) {
                        '=' => strcasecmp(trim((string) $userValue), trim((string) $ruleValue)) === 0,
                        '!=' => strcasecmp(trim((string) $userValue), trim((string) $ruleValue)) !== 0,
                        '>=' => (float) $userValue >= (float) $ruleValue,
                        '<=' => (float) $userValue <= (float) $ruleValue,
                        '>' => (float) $userValue > (float) $ruleValue,
                        '<' => (float) $userValue < (float) $ruleValue,
                        default => false,
                    };

                    if (! $conditionMet) {
                        $matchesAllRules = false;
                        break;
                    }
                }

                // Synchronize the task's `assign_to` column safely
                if ($matchesAllRules) {
                    Log::info("Rules matched user {$user->id}");
                    // Assign to this user ONLY IF the task is currently unassigned or already theirs
                    if (is_null($task->assigned_to) || $task->assigned_to === $user->id) {
                        if ($task->assigned_to !== $user->id) {
                            $task->update(['assigned_to' => $user->id]);
                        }
                    }
                } else {
                    // If they no longer match, and they were the exact user assigned, reset it to null
                    if ($task->assigned_to === $user->id) {
                        $task->update(['assigned_to' => null]);
                    }
                }
            }
        });
    }
}