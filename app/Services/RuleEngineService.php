<?php

namespace App\Services;

use App\Models\TaskAssignmentRule;
use App\Models\Task;
use App\Models\User;

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
        
}