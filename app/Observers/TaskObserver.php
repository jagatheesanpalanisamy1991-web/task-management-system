<?php

namespace App\Observers;
use Illuminate\Support\Facades\Cache;
use App\Models\Task;

class TaskObserver
{
    public function updated(Task $task): void
    {
        if ($task->wasChanged('assigned_to')) {
            $original = $task->getOriginal('assigned_to');
            $current = $task->assigned_to;

            if ($original) {
                Cache::forget("user:{$original}:active_tasks_count");
            }
            if ($current) {
                Cache::forget("user:{$current}:active_tasks_count");
            }
        }
    }
}
