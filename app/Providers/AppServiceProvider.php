<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\TaskAssignmentRule;
use App\Observers\UserObserver;
use App\Observers\TaskAssignmentRuleObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        TaskAssignmentRule::observe(TaskAssignmentRuleObserver::class);

    }
}
