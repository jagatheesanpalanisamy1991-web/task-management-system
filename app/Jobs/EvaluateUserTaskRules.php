<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use App\Services\RuleEngineService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\TaskListUpdated;
use Illuminate\Support\Facades\Log;

class EvaluateUserTaskRules implements ShouldQueue
{
    use Queueable,Dispatchable, InteractsWithQueue,SerializesModels;

    protected User $user;
    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(RuleEngineService $ruleEngine): void
    {
        $ruleEngine->evaluateUser($this->user);
        //Log::info("Broadcat started..");
        //TaskListUpdated::dispatch($this->user->id);
        //Log::info("Broadcat completed.");
    }
}
