<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\RuleEngineService;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;


class RuleEngineServiceTest extends TestCase
{
    //use RefreshDatabase;

    private RuleEngineService $engine;
 
    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = app(RuleEngineService::class);
    }
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $this->assertTrue(true);
    }
    public function test_rule_engine_with_local_data(): void
    {
        $engine = app(\App\Services\RuleEngineService::class);

        $tasks = Task::with('taskRules')
            ->where('assignment_pending', true)
            ->limit(10)
            ->get();
        $this->assertNotEmpty($tasks);

        foreach ($tasks as $task) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $start = microtime(true);

            $user = $engine->findEligibleUser($task);

            $duration = microtime(true) - $start;
            $queries = DB::getQueryLog();
            dump([
                'task_id' => $task->id,
                'matched_user' => $user?->id,
                'time_ms' => round($duration * 1000, 2),
                'query_count' => count($queries),
                'queries' => $queries,
            ]);
        }

        $this->assertTrue(true);
    }
}
