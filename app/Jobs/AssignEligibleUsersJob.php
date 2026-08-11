<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Task;
use App\Models\TaskAssignmentRule;
use App\Services\RuleEngineService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class AssignEligibleUsersJob implements ShouldQueue
{
    use Queueable,Dispatchable, InteractsWithQueue,SerializesModels;

    protected Task $task;
    /**
     * Create a new job instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     */
    public function handle(RuleEngineService $ruleEngine): void
    {
        $ruleEngine->evaluateTask($this->task);
    }
}
