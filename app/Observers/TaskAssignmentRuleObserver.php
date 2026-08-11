<?php

namespace App\Observers;
use App\Models\TaskAssignmentRule;
use App\Jobs\AssignEligibleUsersJob;
use Illuminate\Support\Facades\DB;

class TaskAssignmentRuleObserver
{
    public function created(TaskAssignmentRule $taskRule): void
    {
        $this->evaluateTask($taskRule);
    }

    public function updated(TaskAssignmentRule $taskRule): void
    {
        $this->evaluateTask($taskRule);
    }

    public function deleted(TaskAssignmentRule $taskRule): void
    {
        $this->evaluateTask($taskRule);
    }

    protected function evaluateTask(TaskAssignmentRule $taskRule): void
    {
        if ($taskRule) {
            DB::afterCommit(function () use ($taskRule) {
                AssignEligibleUsersJob::dispatch($taskRule->task);
            });
        }
    }
}
