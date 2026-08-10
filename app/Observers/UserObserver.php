<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Jobs\EvaluateUserTaskRules;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        //
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $relevantAttributes = ['department', 'years_experience', 'location'];
        if ($user->wasChanged($relevantAttributes)) {
            
            DB::afterCommit(function () use ($user) {
                EvaluateUserTaskRules::dispatch($user);
            });
        }

    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
